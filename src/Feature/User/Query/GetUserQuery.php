<?php

declare(strict_types=1);

namespace App\Feature\User\Query;

final readonly class GetUserQuery
{
    public function __construct(private string $userId)
    {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
