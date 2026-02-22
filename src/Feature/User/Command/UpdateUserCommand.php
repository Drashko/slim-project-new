<?php

declare(strict_types=1);

namespace App\Feature\User\Command;

final class UpdateUserCommand
{
    /**
     * @param string[]|null $roles
     */
    public function __construct(
        private readonly string $userId,
        private readonly ?string $email = null,
        private readonly ?string $password = null,
        private ?array $roles = null,
        private readonly ?string $status = null
    ) {
        $this->roles = $this->roles !== null ? $this->normalizeRoles($this->roles) : null;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string[]|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getStatus(): ?string
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
