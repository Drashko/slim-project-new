<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission;

final class ValidatePermissionRequest
{
    /**
     * @return array<string, string>
     */
    public function validate(DtoPermissionRequest $input): array
    {
        $errors = [];

        if (mb_strlen($input->getSearch()) > 255) {
            $errors['search'] = 'Search query is too long.';
        }

        foreach ($input->getGranted() as $value) {
            if ($value === '') {
                $errors['granted'] = 'Permission identifiers must be non-empty strings.';
                break;
            }
        }

        return $errors;
    }
}
