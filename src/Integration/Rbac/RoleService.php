<?php

declare(strict_types=1);

namespace App\Integration\Rbac;

use App\Domain\Role\RoleRepositoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class RoleService
{
    public function __construct(
        private RoleRepositoryInterface $roles,
        private CacheInterface $cache
    ) {
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    public function permissionsForRoles(array $roles, ?int $rolesVersion = null): array
    {
        $normalized = $this->normalizeRoles($roles);
        if ($normalized === []) {
            return [];
        }

        $cacheKey = $this->buildCacheKey($normalized, $rolesVersion);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($normalized): array {
            $permissions = [];

            foreach ($normalized as $roleKey) {
                $role = $this->roles->findByKey($roleKey);
                if ($role === null) {
                    continue;
                }

                foreach ($role->getPermissions() as $permission) {
                    if (!method_exists($permission, 'getKey')) {
                        continue;
                    }
                    $permissionKey = (string) $permission->getKey();
                    if ($permissionKey !== '') {
                        $permissions[] = $permissionKey;
                    }
                }
            }

            return array_values(array_unique($permissions));
        });
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            if (!is_string($role)) {
                continue;
            }

            $value = strtolower(trim($role));
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    /**
     * @param string[] $normalizedRoles
     */
    private function buildCacheKey(array $normalizedRoles, ?int $rolesVersion): string
    {
        $payload = json_encode([$normalizedRoles, $rolesVersion ?? 0]);
        if ($payload === false) {
            $payload = implode(',', $normalizedRoles) . '|' . (string) ($rolesVersion ?? 0);
        }

        return 'rbac.permissions.' . md5($payload);
    }
}
