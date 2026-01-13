<?php

declare(strict_types=1);

namespace App\Domain\Auth;

final class Identity
{
    public function __construct(
        private readonly string $userId,
        private readonly string $email,
        private array $roles
    ) {
        $this->roles = array_values(array_unique($roles));
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function addRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->userId,
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }
}
