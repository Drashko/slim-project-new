<?php

declare(strict_types=1);

namespace App\Web\Shared;

use App\Web\Public\DTO\RegisterFormData;

final class PublicUserResolver
{
    private function __construct()
    {
    }

    public static function resolve(?array $user): ?array
    {
        if (!is_array($user)) {
            return null;
        }

        $roles = self::normalizeRoles($user['roles'] ?? []);
        if (self::hasAdminRole($roles)) {
            return null;
        }

        return $user;
    }

    /**
     * @return string[]
     */
    private static function normalizeRoles(mixed $roles): array
    {
        if ($roles === null) {
            return [];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $normalized = [];
        foreach ($roles as $role) {
            if (is_scalar($role)) {
                $normalized[] = trim((string) $role);
            }
        }

        return array_values(array_filter($normalized, static fn(string $role): bool => $role !== ''));
    }

    /**
     * @param string[] $roles
     */
    private static function hasAdminRole(array $roles): bool
    {
        return in_array(RegisterFormData::ROLE_ADMIN, $roles, true);
    }
}
