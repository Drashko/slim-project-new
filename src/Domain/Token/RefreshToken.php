<?php

declare(strict_types=1);

namespace App\Domain\Token;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Stored refresh token model.
 *
 * Security note:
 * - We never store the *plain* refresh token in the database.
 * - We store only a SHA-256 hex hash of it (tokenHash).
 * - Rotation is supported via (revokedAt, replacedBy).
 * - Reuse detection is supported via familyId.
 */
class RefreshToken
{
    private string $id;

    /**
     * SHA-256 (hex) of the plain refresh token.
     */
    private string $tokenHash;

    private string $userId;

    private DateTimeImmutable $expiresAt;

    private DateTimeImmutable $createdAt;

    private ?DateTimeImmutable $revokedAt = null;

    private ?string $replacedBy = null;

    /**
     * Stable id for a refresh-token "family" (session).
     * All rotated tokens share the same familyId.
     */
    private string $familyId;

    public function __construct(string $tokenHash, string $userId, DateTimeImmutable $expiresAt, ?string $familyId = null)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->tokenHash = $tokenHash;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable('now');
        $this->familyId = $familyId ?: $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getFamilyId(): string
    {
        return $this->familyId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function getReplacedBy(): ?string
    {
        return $this->replacedBy;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function revoke(DateTimeImmutable $now, ?string $replacedBy = null): void
    {
        if ($this->revokedAt !== null) {
            return;
        }

        $this->revokedAt = $now;
        $this->replacedBy = $replacedBy;
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }
}
