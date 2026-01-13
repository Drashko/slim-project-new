<?php

declare(strict_types=1);

namespace App\Feature\Admin\Audit\Handler;

use App\Domain\Shared\Event\DomainEventLog;
use App\Feature\Admin\Audit\Command\ListDomainEventLogsCommand;
use App\Feature\Admin\Audit\ListDomainEventLogsResult;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ListDomainEventLogsHandler
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function handle(ListDomainEventLogsCommand $command): ListDomainEventLogsResult
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->from(DomainEventLog::class, 'log')
            ->select('log')
            ->orderBy('log.occurredAt', 'DESC');

        if ($command->getAggregateId() !== null) {
            $qb->andWhere('log.aggregateId = :aggregateId')->setParameter('aggregateId', $command->getAggregateId());
        }

        if ($command->getAggregateType() !== null) {
            $qb->andWhere('log.aggregateType = :aggregateType')->setParameter('aggregateType', $command->getAggregateType());
        }

        if ($command->getEventType() !== null) {
            $qb->andWhere('log.eventType = :eventType')->setParameter('eventType', $command->getEventType());
        }

        if ($command->getProcessed() !== null) {
            $qb->andWhere('log.processed = :processed')->setParameter('processed', $command->getProcessed());
        }

        $query = $qb
            ->setFirstResult($command->getOffset())
            ->setMaxResults($command->getPageSize())
            ->getQuery();

        $countQb = clone $qb;
        $count = (int) $countQb->select('COUNT(log.id)')->resetDQLPart('orderBy')->getQuery()->getSingleScalarResult();

        $logs = $query->getResult();

        return new ListDomainEventLogsResult($logs, $count, $command->getPage(), $command->getPageSize());
    }
}
