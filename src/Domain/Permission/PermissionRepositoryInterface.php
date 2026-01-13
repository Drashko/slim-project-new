<?php

declare(strict_types=1);

namespace App\Domain\Permission;

interface PermissionRepositoryInterface
{
    public function add(PermissionInterface $permission): void;

    public function remove(PermissionInterface $permission): void;

    public function findByKey(string $key): ?PermissionInterface;

    /**
     * @return PermissionInterface[]
     */
    public function all(): array;

    public function flush(): void;
}
