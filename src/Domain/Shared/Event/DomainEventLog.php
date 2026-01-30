<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

use DateTimeImmutable;
class DomainEventLog
{
    private string $id;

    private string $aggregateId;

    private string $aggregateType;

    private string $eventType;

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    private DateTimeImmutable $occurredAt;

    private bool $processed = false;

    private ?DateTimeImmutable $processedAt = null;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        string $aggregateId,
        string $aggregateType,
        string $eventType,
        array $payload,
        DateTimeImmutable $occurredAt
    ) {
        $this->aggregateId = $aggregateId;
        $this->aggregateType = $aggregateType;
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->occurredAt = $occurredAt;
    }

    public static function recordFromEvent(DomainEventWithPayload $event): self
    {
        return new self(
            $event->getAggregateId(),
            $event->getAggregateType(),
            $event->getEventType(),
            $event->getPayload(),
            $event->getOccurredAt(),
        );
    }

    public function markProcessed(DateTimeImmutable $processedAt): void
    {
        $this->processed = true;
        $this->processedAt = $processedAt;
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

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }
}
