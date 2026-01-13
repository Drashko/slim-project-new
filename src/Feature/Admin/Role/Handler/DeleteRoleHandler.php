<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Handler;

use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\DeleteRoleCommand;

final readonly class DeleteRoleHandler
{
    public function __construct(private RoleRepositoryInterface $roles)
    {
    }

    public function handle(DeleteRoleCommand $command): void
    {
        $roleKey = trim($command->getRoleKey());

        if ($roleKey === '') {
            throw new DomainException('admin.roles.errors.key_required');
        }

        $role = $this->roles->findByKey($roleKey);
        if (!$role instanceof RoleInterface) {
            throw new DomainException('admin.roles.errors.not_found');
        }

        if ($role->isCritical()) {
            throw new DomainException('admin.roles.errors.delete_critical');
        }

        $role->setPermissions([]);
        $this->roles->remove($role);
        $this->roles->flush();
    }
}
