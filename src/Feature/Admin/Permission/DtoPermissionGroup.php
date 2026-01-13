<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission;

final class DtoPermissionGroup
{
    /**
     * @param DtoPermission[] $permissions
     */
    public function __construct(
        private string $id,
        private string $label,
        private string $description,
        private array $permissions
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return DtoPermission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'permissions' => array_map(static fn(DtoPermission $permission): array => $permission->toArray(), $this->permissions),
        ];
    }
}
