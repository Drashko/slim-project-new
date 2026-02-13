<?php

declare(strict_types=1);

namespace App\Feature\Register;

final class ValidateRegisterInput
{
    /**
     * @var string[]
     */
    private array $allowedRoles;

    /**
     * @param string[] $allowedRoles
     */
    public function __construct(array $allowedRoles = ['user', 'customer', 'admin', 'super_admin'])
    {
        $this->allowedRoles = array_map(static fn(string $role): string => strtolower(trim($role)), $allowedRoles);
    }

    /**
     * @return array<string, string>
     */
    public function validate(DtoRegisterUserInput $input): array
    {
        $errors = [];
        if ($input->getEmail() === '' || filter_var($input->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if ($input->getPassword() === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($input->getPassword() !== $input->getConfirmPassword()) {
            $errors['confirm_password'] = 'Passwords must match.';
        }

        foreach ($input->getRoles() as $role) {
            if (!in_array(strtolower($role), $this->allowedRoles, true)) {
                $errors['roles'] = 'One or more roles are not permitted.';
                break;
            }
        }

        return $errors;
    }
}
