<?php

declare(strict_types=1);

namespace App\Feature\User\Command;

final readonly class DeleteUserCommand
{
    public function __construct(private string $userId)
    {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
