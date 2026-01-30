<?php

declare(strict_types=1);

namespace App\Web\Public\Form;

final class RegisterUserType
{
    private const ROLE_CHOICES = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_API'];

    /**
     * @return array{data: array<string, string>, roles: string[], errors: array<string, string>}
     */
    public function submit(array $input): array
    {
        $data = [
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'confirm_password' => (string) ($input['confirm_password'] ?? ''),
            'role' => strtoupper(trim((string) ($input['role'] ?? self::ROLE_CHOICES[0]))),
        ];

        $errors = [];
        if ($data['email'] === '' || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Provide a valid email address.';
        }

        if ($data['password'] === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords must match.';
        }

        if (!in_array($data['role'], self::ROLE_CHOICES, true)) {
            $errors['role'] = sprintf('Role must be one of: %s.', implode(', ', self::ROLE_CHOICES));
            $data['role'] = self::ROLE_CHOICES[0];
        }

        return [
            'data' => $data,
            'roles' => [$data['role']],
            'errors' => $errors,
        ];
    }

    /**
     * @return string[]
     */
    public function choices(): array
    {
        return self::ROLE_CHOICES;
    }
}
