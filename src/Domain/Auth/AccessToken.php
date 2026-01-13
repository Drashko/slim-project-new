<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use DateTimeImmutable;
use JsonSerializable;

final readonly class AccessToken implements JsonSerializable
{
    public function __construct(
        private string            $token,
        private DateTimeImmutable $expiresAt
    ) {
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'expires_at' => $this->expiresAt->format(DATE_ATOM),
        ];
    }
}
