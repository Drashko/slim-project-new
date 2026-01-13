<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Command;

final readonly class CreateRoleCommand
{
    /**
     * @param string[] $permissions
     */
    public function __construct(
        private string $key,
        private string $name,
        private string $description,
        private array $permissions,
        private bool $critical = false
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }
}
