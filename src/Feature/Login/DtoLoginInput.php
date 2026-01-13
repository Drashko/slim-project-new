<?php

declare(strict_types=1);

namespace App\Feature\Login;

final class DtoLoginInput
{
    public function __construct(private string $email, private string $password)
    {
        $this->email = strtolower(trim($this->email));
        $this->password = (string) $this->password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
