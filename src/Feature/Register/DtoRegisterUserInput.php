<?php

declare(strict_types=1);

namespace App\Feature\Register;

final class DtoRegisterUserInput
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $email,
        private string $password,
        private string $confirmPassword,
        private array $roles
    ) {
        $this->email = strtolower(trim($this->email));
        $this->password = (string) $this->password;
        $this->confirmPassword = (string) $this->confirmPassword;
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

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
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
            $role = strtoupper(trim((string) $role));
            if ($role !== '') {
                $normalized[$role] = $role;
            }
        }

        if ($normalized === []) {
            $normalized['ROLE_USER'] = 'ROLE_USER';
        }

        return array_values($normalized);
    }
}
