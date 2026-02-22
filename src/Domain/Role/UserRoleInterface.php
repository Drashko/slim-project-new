<?php

declare(strict_types=1);

namespace App\Domain\Role;

use App\Domain\User\UserInterface;

interface UserRoleInterface
{
    public function getRole(): string;

    public function getUser(): UserInterface;
}
