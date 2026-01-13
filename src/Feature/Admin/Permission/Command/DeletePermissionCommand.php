<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Command;

final readonly class DeletePermissionCommand
{
    public function __construct(private string $key)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
