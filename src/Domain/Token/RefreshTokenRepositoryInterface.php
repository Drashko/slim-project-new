<?php

declare(strict_types=1);

namespace App\Domain\Token;

use DateTimeImmutable;

interface RefreshTokenRepositoryInterface
{
    public function persist(string $token, string $userId, DateTimeImmutable $expiresAt): RefreshToken;

    public function find(string $token): ?RefreshToken;

    public function revoke(string $token): void;

    public function purgeExpired(DateTimeImmutable $now): int;
}
