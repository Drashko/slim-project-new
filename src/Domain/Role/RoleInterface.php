<?php

declare(strict_types=1);

namespace App\Domain\Role;

use App\Domain\Permission\PermissionInterface;

interface RoleInterface
{
    public function getId(): string;

    public function getKey(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function isCritical(): bool;

    public function markCritical(bool $critical = true): void;

    public function getMemberCount(): int;

    public function setMemberCount(int $memberCount): void;

    /**
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @param PermissionInterface[] $permissions
     */
    public function setPermissions(array $permissions): void;

    public function addPermission(PermissionInterface $permission): void;

    public function removePermission(PermissionInterface $permission): void;
}
