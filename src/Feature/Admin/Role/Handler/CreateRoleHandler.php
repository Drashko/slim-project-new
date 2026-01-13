<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Handler;

use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Role\Role;
use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\CreateRoleCommand;

final readonly class CreateRoleHandler
{
    public function __construct(
        private RoleRepositoryInterface $roles,
        private PermissionRepositoryInterface $permissions
    ) {
    }

    public function handle(CreateRoleCommand $command): RoleInterface
    {
        $key = trim($command->getKey());
        $name = trim($command->getName());

        if ($key === '') {
            throw new DomainException('admin.roles.errors.key_required');
        }

        if ($name === '') {
            throw new DomainException('admin.roles.errors.name_required');
        }

        if ($this->roles->findByKey($key) instanceof RoleInterface) {
            throw new DomainException('admin.roles.errors.key_exists');
        }

        $permissions = $this->resolvePermissions($command->getPermissions());

        $role = new Role($key, $name, $command->getDescription(), $command->isCritical());
        $role->setPermissions($permissions);

        $this->roles->add($role);
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
