<?php

declare(strict_types=1);

namespace App\Feature\Admin\Role\Handler;

use App\Feature\Admin\Role\Command\ResolveSelectedRoleCommand;

final readonly class ResolveSelectedRoleHandler
{
    public function handle(ResolveSelectedRoleCommand $command): string
    {
        $available = array_values(array_filter($command->getAvailable(), static fn($value): bool => is_string($value) && $value !== ''));
        $requested = trim($command->getRequested());

        if ($requested !== '' && in_array($requested, $available, true)) {
            return $requested;
        }

        return $available[0] ?? '';
    }
}
