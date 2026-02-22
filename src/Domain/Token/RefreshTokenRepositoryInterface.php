<?php

declare(strict_types=1);

namespace App\Domain\Token;

use DateTimeImmutable;

interface RefreshTokenRepositoryInterface
{
    /**
     * Stores a refresh token (plain token provided) by hashing it before persisting.
     * Returns the persisted entity.
     */
    public function persist(string $plainToken, string $userId, DateTimeImmutable $expiresAt, ?string $familyId = null): RefreshToken;

    /**
     * Finds a refresh token by hashing the provided plain token.
     */
    public function find(string $plainToken): ?RefreshToken;

    /**
     * Marks a refresh token revoked.
     */
    public function revokeById(string $id, DateTimeImmutable $now, ?string $replacedBy = null): void;

    /**
     * Revokes the whole family (session) by family id.
     */
    public function revokeFamily(string $familyId, DateTimeImmutable $now): int;

    public function purgeExpired(DateTimeImmutable $now): int;
}
