<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Handler;

use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\DeletePermissionCommand;

final readonly class DeletePermissionHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissions,
        private RoleRepositoryInterface $roles,
    ) {
    }

    public function handle(DeletePermissionCommand $command): void
    {
        $key = trim($command->getKey());

        if ($key === '') {
            throw new DomainException('admin.permissions.errors.key_required');
        }

        $permission = $this->permissions->findByKey($key);
        if (!$permission instanceof PermissionInterface) {
            throw new DomainException('admin.permissions.errors.not_found');
        }

        foreach ($this->roles->all() as $role) {
            if (!$role instanceof RoleInterface) {
                continue;
            }

            $role->removePermission($permission);
        }

        $this->roles->flush();

        $this->permissions->remove($permission);
        $this->permissions->flush();
    }
}
