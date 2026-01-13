<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Command;

final readonly class ResolveSelectedRoleCommand
{
    /**
     * @param string[] $available
     */
    public function __construct(private string $requested, private array $available)
    {
    }

    public function getRequested(): string
    {
        return $this->requested;
    }

    /**
     * @return string[]
     */
    public function getAvailable(): array
    {
        return $this->available;
    }
}
