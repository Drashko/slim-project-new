<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'domain_event_log')]
#[ORM\Index(columns: ['aggregate_type', 'aggregate_id'], name: 'idx_aggregate')]
#[ORM\Index(columns: ['processed', 'occurred_at'], name: 'idx_processed')]
class DomainEventLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private string $id;

    #[ORM\Column(name: 'aggregate_id', type: 'string', length: 64)]
    private string $aggregateId;

    #[ORM\Column(name: 'aggregate_type', type: 'string', length: 255)]
    private string $aggregateType;

    #[ORM\Column(name: 'event_type', type: 'string', length: 255)]
    private string $eventType;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'occurred_at', type: 'datetime_immutable', precision: 6, options: ['comment' => 'When the event occurred'])]
    private DateTimeImmutable $occurredAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $processed = false;

    #[ORM\Column(name: 'processed_at', type: 'datetime_immutable', precision: 6, nullable: true, options: ['comment' => 'When the event was processed'])]
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
