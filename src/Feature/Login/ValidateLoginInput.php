<?php

declare(strict_types=1);

namespace App\Feature\Login;

final class ValidateLoginInput
{
    /**
     * @return array<string, string>
     */
    public function validate(DtoLoginInput $input): array
    {
        $errors = [];
        if ($input->getEmail() === '' || filter_var($input->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'A valid email address is required.';
        }

        if ($input->getPassword() === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }
}
