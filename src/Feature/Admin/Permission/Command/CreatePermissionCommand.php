<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Command;

final readonly class CreatePermissionCommand
{
    public function __construct(private string $key, private string $label)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
