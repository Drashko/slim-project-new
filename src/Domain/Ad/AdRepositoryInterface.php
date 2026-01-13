<?php

declare(strict_types=1);

namespace App\Domain\Ad;

interface AdRepositoryInterface
{
    public function add(AdInterface $ad): void;

    public function remove(AdInterface $ad): void;

    public function find(string $id): ?AdInterface;

    /**
     * @return AdInterface[]
     */
    public function findByUser(string $userId): array;

    /**
     * @return AdInterface[]
     */
    public function findByFilters(
        ?string $category,
        ?string $status,
        ?string $userFilter,
        ?\DateTimeImmutable $fromDate,
        ?\DateTimeImmutable $toDate,
        ?string $ownerId = null
    ): array;

    /**
     * @return AdInterface[]
     */
    public function all(): array;

    public function flush(): void;
}
