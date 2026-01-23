<?php

declare(strict_types=1);

namespace App\Integration\Repository;

use App\Domain\Token\RefreshToken;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use DateTimeImmutable;

final class InMemoryRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var array<string,RefreshToken>
     */
    private array $tokens = [];

    public function persist(string $token, string $userId, DateTimeImmutable $expiresAt): RefreshToken
    {
        $refreshToken = new RefreshToken($token, $userId, $expiresAt);
        $this->tokens[$token] = $refreshToken;

        return $refreshToken;
    }

    public function find(string $token): ?RefreshToken
    {
        return $this->tokens[$token] ?? null;
    }

    public function revoke(string $token): void
    {
        unset($this->tokens[$token]);
    }

    public function purgeExpired(DateTimeImmutable $now): int
    {
        $removed = 0;

        foreach ($this->tokens as $token => $refreshToken) {
            if ($refreshToken->isExpired($now)) {
                unset($this->tokens[$token]);
                $removed++;
            }
        }

        return $removed;
    }
}
