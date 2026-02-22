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
    private array $tokensByHash = [];

    public function persist(string $plainToken, string $userId, DateTimeImmutable $expiresAt, ?string $familyId = null): RefreshToken
    {
        $hash = hash('sha256', $plainToken);
        $refreshToken = new RefreshToken($hash, $userId, $expiresAt, $familyId);
        $this->tokensByHash[$hash] = $refreshToken;

        return $refreshToken;
    }

    public function find(string $plainToken): ?RefreshToken
    {
        $hash = hash('sha256', $plainToken);

        return $this->tokensByHash[$hash] ?? null;
    }

    public function revokeById(string $id, DateTimeImmutable $now, ?string $replacedBy = null): void
    {
        foreach ($this->tokensByHash as $hash => $token) {
            if ($token->getId() === $id) {
                $token->revoke($now, $replacedBy);
                $this->tokensByHash[$hash] = $token;
                return;
            }
        }
    }

    public function revokeFamily(string $familyId, DateTimeImmutable $now): int
    {
        $count = 0;
        foreach ($this->tokensByHash as $hash => $token) {
            if ($token->getFamilyId() === $familyId && !$token->isRevoked()) {
                $token->revoke($now);
                $this->tokensByHash[$hash] = $token;
                $count++;
            }
        }

        return $count;
    }

    public function purgeExpired(DateTimeImmutable $now): int
    {
        $removed = 0;

        foreach ($this->tokensByHash as $hash => $refreshToken) {
            if ($refreshToken->isExpired($now) || $refreshToken->isRevoked()) {
                unset($this->tokensByHash[$hash]);
                $removed++;
            }
        }

        return $removed;
    }
}
