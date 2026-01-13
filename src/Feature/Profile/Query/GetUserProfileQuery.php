<?php

declare(strict_types=1);

namespace App\Feature\Profile\Query;

final readonly class GetUserProfileQuery
{
    public function __construct(private string $userId)
    {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
