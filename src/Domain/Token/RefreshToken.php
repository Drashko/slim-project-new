<?php

declare(strict_types=1);

namespace App\Domain\Token;

use DateTimeImmutable;
class RefreshToken
{
    private string $token;

    private string $userId;

    private DateTimeImmutable $expiresAt;

    private DateTimeImmutable $createdAt;

    public function __construct(string $token, string $userId, DateTimeImmutable $expiresAt)
    {
        $this->token = $token;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable('now');
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }
}
