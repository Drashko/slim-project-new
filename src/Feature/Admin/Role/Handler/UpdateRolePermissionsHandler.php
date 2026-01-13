<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Handler;

use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\UpdateRolePermissionsCommand;

final readonly class UpdateRolePermissionsHandler
{
    public function __construct(
        private RoleRepositoryInterface $roles,
        private PermissionRepositoryInterface $permissions
    ) {
    }

    public function handle(UpdateRolePermissionsCommand $command): RoleInterface
    {
        $roleKey = trim($command->getRoleKey());
        if ($roleKey === '') {
            throw new DomainException('admin.roles.errors.key_required');
        }

        $role = $this->roles->findByKey($roleKey);
        if (!$role instanceof RoleInterface) {
            throw new DomainException('admin.roles.errors.not_found');
        }

        $role->setPermissions($this->resolvePermissions($command->getPermissions()));
        $this->roles->flush();

        return $role;
    }

    /**
     * @param string[] $permissionKeys
     * @return PermissionInterface[]
     */
    private function resolvePermissions(array $permissionKeys): array
    {
        $resolved = [];

        foreach ($permissionKeys as $key) {
            $permission = $this->permissions->findByKey($key);

            if (!$permission instanceof PermissionInterface) {
                throw new DomainException('admin.roles.errors.permission_missing');
            }

            $resolved[$permission->getKey()] = $permission;
        }

        return array_values($resolved);
    }
}
