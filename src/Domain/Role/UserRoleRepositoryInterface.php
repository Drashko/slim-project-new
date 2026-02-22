<?php

declare(strict_types=1);

namespace App\Domain\Role;

interface UserRoleRepositoryInterface
{
    public function add(UserRoleInterface $userRole): void;

    public function remove(UserRoleInterface $userRole): void;

    /**
     * @return UserRoleInterface[]
     */
    public function findByUserId(string $userId): array;

    public function findByUserIdAndRole(string $userId, string $role): ?UserRoleInterface;

    public function flush(): void;
}
