<?php

declare(strict_types=1);

namespace App\Feature\Login\Handler;

use App\Domain\Shared\Clock;
use App\Domain\Shared\DomainException;
use App\Domain\Token\Identity;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenClaims;
use App\Domain\Token\TokenEncoder;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Login\Command\LoginCommand;
use DateInterval;
use Exception;

final readonly class LoginHandler
{
    public function __construct(
        private UserRepositoryInterface         $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private TokenEncoder                    $tokenEncoder,
        private Clock                           $clock,
        private int                             $accessTokenTtl = 3600,
        private int                             $refreshTokenTtl = 1209600
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(LoginCommand $command): array
    {
        $user = $this->userRepository->findByEmail($command->getEmail());
        if ($user === null || !$user->verifyPassword($command->getPassword())) {
            throw new DomainException('Invalid credentials provided.');
        }

        $identity = new Identity($user->getId(), $user->getEmail(), $user->getRoles(), $user->getRolesVersion());

        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->accessTokenTtl)));
        $claims = new TokenClaims($identity->getUserId(), $identity->getEmail(), $identity->getRoles(), $issuedAt, $expiresAt);
        $accessToken = $this->tokenEncoder->encode($claims);

        $refreshToken = $this->generateRefreshToken();
        $refreshExpiresAt = $issuedAt->add(new DateInterval(sprintf('PT%dS', $this->refreshTokenTtl)));
        $this->refreshTokenRepository->persist($refreshToken, $identity->getUserId(), $refreshExpiresAt);

        return [
            'access_token' => $accessToken->getToken(),
            'expires_at' => $accessToken->getExpiresAt()->format(DATE_ATOM),
            'refresh_token' => $refreshToken,
            'refresh_expires_at' => $refreshExpiresAt->format(DATE_ATOM),
            'user' => $identity->toArray(),
        ];
    }

    private function generateRefreshToken(): string
    {
        try {
            return bin2hex(random_bytes(64));
        } catch (Exception $exception) {
            throw new DomainException('Unable to generate secure refresh token: ' . $exception->getMessage());
        }
    }
}
