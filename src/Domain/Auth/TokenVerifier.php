<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Shared\Clock;
use App\Domain\Shared\DomainException;

final readonly class TokenVerifier
{
    public function __construct(
        private TokenEncoder $encoder,
        private Clock        $clock
    ) {
    }

    public function verify(string $token): Identity
    {
        $claims = $this->encoder->decode($token);
        if ($claims->getExpiresAt() <= $this->clock->now()) {
            throw new DomainException('Token has expired.');
        }

        return new Identity($claims->getUserId(), $claims->getEmail(), $claims->getRoles());
    }
}
