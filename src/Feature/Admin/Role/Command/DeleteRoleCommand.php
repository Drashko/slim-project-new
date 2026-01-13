<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Command;

final readonly class DeleteRoleCommand
{
    public function __construct(private string $roleKey)
    {
    }

    public function getRoleKey(): string
    {
        return $this->roleKey;
    }
}
