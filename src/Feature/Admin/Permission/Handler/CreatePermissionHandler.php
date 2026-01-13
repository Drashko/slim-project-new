<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Handler;

use App\Domain\Permission\Permission;
use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\CreatePermissionCommand;

final readonly class CreatePermissionHandler
{
    public function __construct(private PermissionRepositoryInterface $permissions)
    {
    }

    public function handle(CreatePermissionCommand $command): PermissionInterface
    {
        $key = trim($command->getKey());
        $label = trim($command->getLabel());

        if ($key === '') {
            throw new DomainException('admin.permissions.errors.key_required');
        }

        if ($this->permissions->findByKey($key) instanceof PermissionInterface) {
            throw new DomainException('admin.permissions.errors.key_exists');
        }

        $permission = new Permission($key, $label);
        $this->permissions->add($permission);
        $this->permissions->flush();

        return $permission;
    }
}
