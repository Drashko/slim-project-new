<?php

declare(strict_types=1);

namespace App\Web\Auth\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterFormData
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_API = 'ROLE_API';

    public const ACCOUNT_TYPE_CHOICES = [
        'auth.register.account_type.standard' => self::ROLE_USER,
        'auth.register.account_type.admin' => self::ROLE_ADMIN,
        'auth.register.account_type.api' => self::ROLE_API,
    ];

    private const ROLE_MAP = [
        self::ROLE_USER => [self::ROLE_USER],
        self::ROLE_ADMIN => [self::ROLE_ADMIN],
        self::ROLE_API => [self::ROLE_API],
    ];

    #[Assert\NotBlank(message: 'auth.register.errors.invalid_email')]
    #[Assert\Email(message: 'auth.register.errors.invalid_email')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'auth.register.errors.password_required')]
    public ?string $password = null;

    #[Assert\NotBlank(message: 'auth.register.errors.password_required')]
    public ?string $confirmPassword = null;

    #[Assert\Choice(choices: [self::ROLE_USER, self::ROLE_ADMIN, self::ROLE_API], message: 'auth.register.errors.account_type_invalid')]
    public ?string $accountType = self::ROLE_USER;

    #[Assert\IsTrue(message: 'auth.register.errors.password_mismatch')]
    public function isPasswordConfirmed(): bool
    {
        if ($this->password === null || $this->confirmPassword === null) {
            return true;
        }

        return hash_equals((string) $this->password, (string) $this->confirmPassword);
    }

    public function getEmail(): string
    {
        return strtolower(trim((string) $this->email));
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $key = strtoupper((string) $this->accountType);

        return self::ROLE_MAP[$key] ?? self::ROLE_MAP[self::ROLE_USER];
    }
}
