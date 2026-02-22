<?php

declare(strict_types=1);

namespace App\Feature\Register\Command;

final readonly class RegisterUserCommand
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $email,
        private string $password,
        private array  $roles = ['user']
    ) {
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
}
