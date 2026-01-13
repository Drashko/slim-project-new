<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

interface DomainEventDispatcherInterface
{
    public function addListener(string $eventClass, callable $listener): void;

    public function dispatch(DomainEvent $event): void;

    /**
     * @param iterable<DomainEvent> $events
     */
    public function dispatchAll(iterable $events): void;
}
