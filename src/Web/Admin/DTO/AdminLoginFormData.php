<?php

declare(strict_types=1);

namespace App\Web\Admin\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class AdminLoginFormData
{
    #[Assert\NotBlank(message: 'auth.login.errors.email_required')]
    #[Assert\Email(message: 'auth.login.errors.invalid_email')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'auth.login.errors.password_required')]
    public ?string $password = null;

    public function getEmail(): string
    {
        return strtolower(trim((string) $this->email));
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }
}
