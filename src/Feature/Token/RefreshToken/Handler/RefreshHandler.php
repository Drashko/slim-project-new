<?php

declare(strict_types=1);

namespace App\Feature\Token\RefreshToken\Handler;

use App\Domain\Shared\Clock;
use App\Domain\Shared\DomainException;
use App\Domain\Token\Identity;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenClaims;
use App\Domain\Token\TokenEncoder;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Token\RefreshToken\Command\RefreshCommand;
use DateInterval;
use Exception;
use Random\RandomException;

final readonly class RefreshHandler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private UserRepositoryInterface         $userRepository,
        private TokenEncoder                    $tokenEncoder,
        private Clock                           $clock,
        private int                             $accessTokenTtl = 3600,
        private int                             $refreshTokenTtl = 1209600
    ) {
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public function handle(RefreshCommand $command): array
    {
        $stored = $this->refreshTokenRepository->find($command->getRefreshToken());
        if ($stored === null) {
            throw new DomainException('Refresh token not found.');
        }

        if ($stored->isExpired($this->clock->now())) {
            $this->refreshTokenRepository->revoke($stored->getToken());
            throw new DomainException('Refresh token expired.');
        }

        $user = $this->userRepository->find($stored->getUserId());
        if ($user === null) {
            throw new DomainException('User not found for refresh token.');
        }

        $identity = new Identity($user->getId(), $user->getEmail(), $user->getRoles());

        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->accessTokenTtl)));
        $claims = new TokenClaims($identity->getUserId(), $identity->getEmail(), $identity->getRoles(), $issuedAt, $expiresAt);
        $accessToken = $this->tokenEncoder->encode($claims);

        // rotate refresh token
        $this->refreshTokenRepository->revoke($stored->getToken());
        $newRefreshToken = bin2hex(random_bytes(64));
        $refreshExpiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->refreshTokenTtl)));
        $this->refreshTokenRepository->persist($newRefreshToken, $identity->getUserId(), $refreshExpiresAt);

        return [
            'access_token' => $accessToken->getToken(),
            'expires_at' => $accessToken->getExpiresAt()->format(DATE_ATOM),
            'refresh_token' => $newRefreshToken,
            'refresh_expires_at' => $refreshExpiresAt->format(DATE_ATOM),
            'user' => $identity->toArray(),
        ];
    }
}
