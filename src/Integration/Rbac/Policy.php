<?php

declare(strict_types=1);

namespace App\Integration\Rbac;

use Laminas\Permissions\Rbac\Rbac;

final readonly class Policy
{
    public function __construct(private Rbac $rbac)
    {
    }

    /**
     * @param string[] $roles
     */
    public function isGranted(array $roles, string $ability): bool
    {
        $ability = $this->normalizeAbility($ability);
        if ($ability === null) {
            return false;
        }

        foreach ($roles as $role) {
            $normalizedRole = $this->normalizeRole($role);
            if ($normalizedRole === null || !$this->rbac->hasRole($normalizedRole)) {
                continue;
            }

            if ($this->rbac->isGranted($normalizedRole, $ability)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeAbility(mixed $ability): ?string
    {
        if (!is_string($ability)) {
            return null;
        }

        $normalized = strtolower(trim($ability));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeRole(mixed $role): ?string
    {
        if (!is_string($role)) {
            return null;
        }

        $normalized = strtoupper(trim($role));

        return $normalized === '' ? null : $normalized;
    }
}
