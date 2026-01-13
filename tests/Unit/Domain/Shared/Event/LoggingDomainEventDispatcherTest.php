<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\Event;

use App\Domain\Shared\Clock;
use App\Domain\Shared\Event\DomainEventLog;
use App\Domain\Shared\Event\DomainEventWithPayload;
use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use App\Domain\Shared\Event\LoggingDomainEventDispatcher;
use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class LoggingDomainEventDispatcherTest extends TestCase
{
    public function testDispatchPersistsAndMarksProcessedLogs(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration([
            __DIR__ . '/../../../../../src/Domain/Shared/Event',
        ], true);

        $entityManager = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            $config,
        );

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $innerDispatcher = new InMemoryDomainEventDispatcher();
        $clock = $this->createMock(Clock::class);
        $now = new DateTimeImmutable('2024-06-02T12:00:00+00:00');
        $clock->method('now')->willReturn($now);

        $dispatcher = new LoggingDomainEventDispatcher($entityManager, $innerDispatcher, $clock);

        $handled = false;
        $innerDispatcher->addListener(StubPayloadEvent::class, static function () use (&$handled): void {
            $handled = true;
        });

        $occurredAt = new DateTimeImmutable('2024-06-01T09:30:00+00:00');
        $event = new StubPayloadEvent(
            'aggregate-123',
            'user',
            'user.created',
            ['foo' => 'bar'],
            $occurredAt,
        );

        $dispatcher->dispatch($event);

        self::assertTrue($handled, 'Listener should be executed');

        $logs = $entityManager->getRepository(DomainEventLog::class)->findAll();
        self::assertCount(1, $logs);

        /** @var DomainEventLog $log */
        $log = $logs[0];
        self::assertSame('aggregate-123', $log->getAggregateId());
        self::assertSame('user', $log->getAggregateType());
        self::assertSame('user.created', $log->getEventType());
        self::assertSame(['foo' => 'bar'], $log->getPayload());
        self::assertSame($occurredAt, $log->getOccurredAt());
        self::assertTrue($log->isProcessed());
        self::assertSame($now, $log->getProcessedAt());
    }
}

final class StubPayloadEvent implements DomainEventWithPayload
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $aggregateId,
        private readonly string $aggregateType,
        private readonly string $eventType,
        private readonly array $payload,
        private readonly DateTimeImmutable $occurredAt,
    ) {
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
