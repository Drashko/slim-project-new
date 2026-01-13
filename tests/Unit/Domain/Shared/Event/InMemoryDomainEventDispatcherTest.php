<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\Event;

use App\Domain\Shared\Event\DomainEvent;
use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class InMemoryDomainEventDispatcherTest extends TestCase
{
    public function testDispatchRunsMatchingListeners(): void
    {
        $dispatcher = new InMemoryDomainEventDispatcher();
        $captured = [];

        $dispatcher->addListener(DomainEvent::class, static function (DomainEvent $event) use (&$captured): void {
            $captured['base'][] = $event;
        });

        $dispatcher->addListener(StubEvent::class, static function (StubEvent $event) use (&$captured): void {
            $captured['specific'][] = $event;
        });

        $event = new StubEvent(new DateTimeImmutable('2024-05-01T12:00:00+00:00'));
        $dispatcher->dispatch($event);

        self::assertCount(1, $captured['base'] ?? []);
        self::assertCount(1, $captured['specific'] ?? []);
        self::assertSame($event, $captured['base'][0]);
        self::assertSame($event, $captured['specific'][0]);
    }

    public function testDispatchAllForwardsMultipleEvents(): void
    {
        $events = [
            new StubEvent(new DateTimeImmutable('2024-05-01T12:00:00+00:00')),
            new StubEvent(new DateTimeImmutable('2024-05-02T12:00:00+00:00')),
        ];

        $dispatcher = new InMemoryDomainEventDispatcher();
        $handled = [];
        $dispatcher->addListener(StubEvent::class, static function (StubEvent $event) use (&$handled): void {
            $handled[] = $event->getOccurredAt()->format(DATE_ATOM);
        });

        $dispatcher->dispatchAll($events);

        self::assertSame([
            '2024-05-01T12:00:00+00:00',
            '2024-05-02T12:00:00+00:00',
        ], $handled);
    }
}

final class StubEvent implements DomainEvent
{
    public function __construct(private readonly DateTimeImmutable $occurredAt)
    {
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
