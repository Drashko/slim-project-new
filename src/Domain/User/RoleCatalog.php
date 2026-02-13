<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\DomainException;

final readonly class RoleCatalog
{
    /**
     * @param string[] $allowedRoles
     */
    public function __construct(
        private array $allowedRoles,
        private string $defaultRole = 'user'
    ) {
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    public function normalize(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            $value = strtolower(trim((string) $role));
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }

        if ($normalized === []) {
            $normalized[$this->defaultRole] = $this->defaultRole;
        }

        return array_values($normalized);
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    public function assertAllowed(array $roles): array
    {
        $normalized = $this->normalize($roles);

        foreach ($normalized as $role) {
            if (!in_array($role, $this->allowedRoles, true)) {
                throw new DomainException(sprintf('Role "%s" is not allowed.', $role));
            }
        }

        return $normalized;
    }

    /**
     * @return string[]
     */
    public function getAllowedRoles(): array
    {
        return $this->allowedRoles;
    }

    public function getDefaultRole(): string
    {
        return $this->defaultRole;
    }
}
