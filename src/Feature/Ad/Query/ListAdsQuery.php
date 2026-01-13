<?php

declare(strict_types=1);

namespace App\Feature\Ad\Query;

final readonly class ListAdsQuery
{
    public function __construct(
        private ?string $userId = null,
        private ?string $category = null,
        private ?string $status = null,
        private ?string $userFilter = null,
        private ?\DateTimeImmutable $fromDate = null,
        private ?\DateTimeImmutable $toDate = null
    ) {
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getUserFilter(): ?string
    {
        return $this->userFilter;
    }

    public function getFromDate(): ?\DateTimeImmutable
    {
        return $this->fromDate;
    }

    public function getToDate(): ?\DateTimeImmutable
    {
        return $this->toDate;
    }

    public function hasFilters(): bool
    {
        return $this->category !== null
            || $this->status !== null
            || $this->userFilter !== null
            || $this->fromDate !== null
            || $this->toDate !== null;
    }
}
