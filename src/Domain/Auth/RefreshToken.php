<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 128)]
    private string $token;

    #[ORM\Column(type: 'guid')]
    private string $userId;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable')]
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
