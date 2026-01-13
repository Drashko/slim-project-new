<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission;

final class DtoPermission
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

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'label' => $this->getLabel(),
        ];
    }
}
