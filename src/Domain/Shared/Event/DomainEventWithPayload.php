<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

/**
 * Domain events that can be logged should expose payload and aggregate metadata.
 */
interface DomainEventWithPayload extends DomainEvent
{
    public function getAggregateId(): string;

    public function getAggregateType(): string;

    public function getEventType(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;
}
