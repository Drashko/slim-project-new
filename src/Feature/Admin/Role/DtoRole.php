<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role;

final class DtoRole
{
    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        private int $members,
        private array $permissions,
        private array $permissionKeys,
        private bool $critical
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMembers(): int
    {
        return $this->members;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return string[]
     */
    public function getPermissionKeys(): array
    {
        return $this->permissionKeys;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'members' => $this->getMembers(),
            'permissions' => $this->getPermissions(),
            'permissionKeys' => $this->getPermissionKeys(),
            'critical' => $this->isCritical(),
        ];
    }
}
