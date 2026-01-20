<?php

declare(strict_types=1);

namespace App\Integration\Rbac;

use App\Domain\Role\RoleRepositoryInterface;

final readonly class Policy
{
    public function __construct(private RoleRepositoryInterface $roles)
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
            if ($normalizedRole === null) {
                continue;
            }

            $roleEntity = $this->roles->findByKey($normalizedRole);
            if ($roleEntity === null) {
                continue;
            }

            foreach ($roleEntity->getPermissions() as $permission) {
                if (method_exists($permission, 'getKey') && $permission->getKey() === $ability) {
                    return true;
                }
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

        $normalized = strtolower(trim($role));

        return $normalized === '' ? null : $normalized;
    }
}
