<?php

declare(strict_types=1);

namespace App\Domain\Role;

interface RoleRepositoryInterface
{
    public function add(RoleInterface $role): void;

    public function remove(RoleInterface $role): void;

    public function findByKey(string $key): ?RoleInterface;

    /**
     * @return RoleInterface[]
     */
    public function all(): array;

    public function flush(): void;
}
