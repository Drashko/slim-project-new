<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use DateTimeImmutable;

final readonly class TokenClaims
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string            $userId,
        private string            $email,
        private array             $roles,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private ?string           $tokenId = null
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }

    public function toArray(): array
    {
        return [
            'sub' => $this->userId,
            'email' => $this->email,
            'roles' => $this->roles,
            'iat' => $this->issuedAt->getTimestamp(),
            'exp' => $this->expiresAt->getTimestamp(),
            'jti' => $this->tokenId,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['sub'],
            (string) $data['email'],
            array_map('strval', $data['roles'] ?? []),
            (new DateTimeImmutable())->setTimestamp((int) $data['iat']),
            (new DateTimeImmutable())->setTimestamp((int) $data['exp']),
            isset($data['jti']) ? (string) $data['jti'] : null
        );
    }
}
