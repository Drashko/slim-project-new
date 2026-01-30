<?php

declare(strict_types=1);

namespace App\Domain\Permission;

use Symfony\Component\Uid\Uuid;

class Permission implements PermissionInterface
{
    private string $id;

    private string $key;

    private string $label;

    public function __construct(string $key, string $label)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->key = strtolower(trim($key));
        $this->setLabel($label);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $label = trim($label);
        if ($label === '') {
            $label = $this->key;
        }

        $this->label = $label;
    }
}
