<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Command;

use App\Feature\Admin\Permission\DtoPermissionRequest;

final readonly class ListPermissionsCommand
{
    public function __construct(private DtoPermissionRequest $input)
    {
    }

    public function getInput(): DtoPermissionRequest
    {
        return $this->input;
    }
}
