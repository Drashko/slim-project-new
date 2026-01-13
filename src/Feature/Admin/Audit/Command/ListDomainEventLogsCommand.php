<?php

declare(strict_types=1);

namespace App\Feature\Admin\Audit\Command;

final class ListDomainEventLogsCommand
{
    private int $page;
    private int $pageSize;
    private ?string $aggregateId;
    private ?string $aggregateType;
    private ?string $eventType;
    private ?bool $processed;

    public function __construct(
        int $page = 1,
        int $pageSize = 50,
        ?string $aggregateId = null,
        ?string $aggregateType = null,
        ?string $eventType = null,
        ?bool $processed = null,
    )
    {
        $this->page = max(1, $page);
        $this->pageSize = max(1, min($pageSize, 200));
        $this->aggregateId = $this->normalizeString($aggregateId);
        $this->aggregateType = $this->normalizeString($aggregateType);
        $this->eventType = $this->normalizeString($eventType);
        $this->processed = $processed;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }

    public function getAggregateId(): ?string
    {
        return $this->aggregateId;
    }

    public function getAggregateType(): ?string
    {
        return $this->aggregateType;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function getProcessed(): ?bool
    {
        return $this->processed;
    }

    private function normalizeString(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return $value !== '' ? $value : null;
    }
}
