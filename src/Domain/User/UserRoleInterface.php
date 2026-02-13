<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRoleInterface
{
    public function getRole(): string;

    public function getUser(): UserInterface;
}
