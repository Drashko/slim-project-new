<?php

declare(strict_types=1);

namespace App\Feature\Login\Command;

final readonly class LoginCommand
{
    public function __construct(
        private string  $email,
        private string  $password,
        private ?string $ipAddress = null
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

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }
}
