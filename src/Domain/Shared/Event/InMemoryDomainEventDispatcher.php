<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

final class InMemoryDomainEventDispatcher implements DomainEventDispatcherInterface
{
    /**
     * @var array<string, callable[]>
     */
    private array $listeners = [];

    /**
     * @param array<string, callable[]> $listeners
     */
    public function __construct(array $listeners = [])
    {
        foreach ($listeners as $eventClass => $eventListeners) {
            foreach ($eventListeners as $listener) {
                $this->addListener((string) $eventClass, $listener);
            }
        }
    }

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass] ??= [];
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(DomainEvent $event): void
    {
        foreach ($this->listeners as $eventClass => $listeners) {
            if (!is_a($event, $eventClass)) {
                continue;
            }

            foreach ($listeners as $listener) {
                $listener($event);
            }
        }
    }

    public function dispatchAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
