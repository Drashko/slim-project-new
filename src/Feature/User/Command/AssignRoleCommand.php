<?php

declare(strict_types=1);

namespace App\Feature\User\Command;

final class AssignRoleCommand
{
    /**
     * @param string[] $roles
     */
    public function __construct(private readonly string $userId, private array $roles)
    {
        $this->roles = $this->normalizeRoles($roles);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            $role = strtolower(trim((string) $role));
            if ($role !== '') {
                $normalized[$role] = $role;
            }
        }

        if ($normalized === []) {
            $normalized['user'] = 'user';
        }

        return array_values($normalized);
    }
}
