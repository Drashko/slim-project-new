<?php

declare(strict_types=1);

namespace App\Feature\Auth\RefreshToken\Command;

final readonly class RefreshCommand
{
    public function __construct(private string $refreshToken)
    {
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
