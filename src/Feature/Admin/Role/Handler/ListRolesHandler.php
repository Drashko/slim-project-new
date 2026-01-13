<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Handler;

use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\DtoRole;

final readonly class ListRolesHandler
{
    public function __construct(private RoleRepositoryInterface $roles)
    {
    }

    /**
     * @return DtoRole[]
     */
    public function handle(ListRolesCommand $command): array
    {
        $result = [];
        foreach ($this->roles->all() as $role) {
            if (!$role instanceof RoleInterface) {
                continue;
            }

            $permissions = [];
            $permissionKeys = [];

            foreach ($role->getPermissions() as $permission) {
                if (!method_exists($permission, 'getLabel')) {
                    continue;
                }

                $permissions[] = $permission->getLabel();
                $permissionKeys[] = method_exists($permission, 'getKey') ? $permission->getKey() : '';
            }

            $result[] = new DtoRole(
                $role->getKey(),
                $role->getName(),
                $role->getDescription(),
                $role->getMemberCount(),
                $permissions,
                $permissionKeys,
                $role->isCritical()
            );
        }

        return $result;
    }
}
