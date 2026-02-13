<?php

declare(strict_types=1);

namespace App\Domain\User;

final class UserRole implements UserRoleInterface
{
    private int $id;

    private User $user;

    private string $role;

    public function __construct(User $user, string $role)
    {
        $this->user = $user;
        $this->role = strtolower(trim($role));
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
