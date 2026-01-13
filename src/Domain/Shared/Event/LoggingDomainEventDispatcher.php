<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

use App\Domain\Shared\Clock;
use Doctrine\ORM\EntityManagerInterface;

final readonly class LoggingDomainEventDispatcher implements DomainEventDispatcherInterface
{
    public function __construct(
        private EntityManagerInterface         $entityManager,
        private DomainEventDispatcherInterface $inner,
        private Clock                          $clock,
    ) {
    }

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->inner->addListener($eventClass, $listener);
    }

    public function dispatch(DomainEvent $event): void
    {
        $log = $event instanceof DomainEventWithPayload
            ? DomainEventLog::recordFromEvent($event)
            : null;

        if ($log !== null) {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }

        $this->inner->dispatch($event);

        if ($log !== null && !$log->isProcessed()) {
            $log->markProcessed($this->clock->now());
            $this->entityManager->flush();
        }
    }

    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
