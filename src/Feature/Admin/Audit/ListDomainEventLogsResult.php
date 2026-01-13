<?php

declare(strict_types=1);

namespace App\Feature\Admin\Audit;

use App\Domain\Shared\Event\DomainEventLog;

final readonly class ListDomainEventLogsResult
{
    /**
     * @param DomainEventLog[] $logs
     */
    public function __construct(
        private array $logs,
        private int $total,
        private int $page,
        private int $pageSize,
    ) {
    }

    /**
     * @return DomainEventLog[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
