<?php

declare(strict_types=1);

namespace App\Integration\Rbac;

final readonly class Policy
{
    public function __construct(private RoleService $roles)
    {
    }

    /**
     * @param string[] $roles
     */
    public function isGranted(array $roles, string $ability, ?int $rolesVersion = null): bool
    {
        $ability = $this->normalizeAbility($ability);
        if ($ability === null) {
            return false;
        }

        $normalizedRoles = [];
        foreach ($roles as $role) {
            $normalizedRole = $this->normalizeRole($role);
            if ($normalizedRole === null) {
                continue;
            }
            $normalizedRoles[] = $normalizedRole;
        }

        if ($ability === 'admin.access' && in_array('role_admin', $normalizedRoles, true)) {
            return true;
        }

        $permissions = $this->roles->permissionsForRoles($normalizedRoles, $rolesVersion);
        foreach ($permissions as $permission) {
            if ($permission === $ability) {
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

        $normalized = strtolower(trim($role));

        return $normalized === '' ? null : $normalized;
    }
}
