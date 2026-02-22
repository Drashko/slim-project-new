<?php

declare(strict_types=1);

namespace App\Feature\Token\Revoke\Command;

final readonly class RevokeCommand
{
    public function __construct(private string $refreshToken)
    {
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
