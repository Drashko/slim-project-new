<?php

declare(strict_types=1);

namespace App\Domain\Permission;

interface PermissionInterface
{
    public function getId(): string;

    public function getKey(): string;

    public function getLabel(): string;

    public function setLabel(string $label): void;
}
