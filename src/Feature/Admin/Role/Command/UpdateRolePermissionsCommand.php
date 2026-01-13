<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Command;

final readonly class UpdateRolePermissionsCommand
{
    /**
     * @param string[] $permissions
     */
    public function __construct(private string $roleKey, private array $permissions)
    {
    }

    public function getRoleKey(): string
    {
        return $this->roleKey;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
