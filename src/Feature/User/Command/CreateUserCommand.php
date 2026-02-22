<?php

declare(strict_types=1);

namespace App\Feature\User\Command;

final class CreateUserCommand
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private readonly string $email,
        private readonly string $password,
        private array $roles = ['user'],
        private readonly string $status = 'Active'
    ) {
        $this->roles = $this->normalizeRoles($roles);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getStatus(): string
    {
        return $this->status;
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
