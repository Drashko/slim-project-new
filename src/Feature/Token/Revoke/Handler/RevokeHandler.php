<?php

declare(strict_types=1);

namespace App\Feature\Token\Revoke\Handler;

use App\Domain\Shared\Clock;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Feature\Token\Revoke\Command\RevokeCommand;

final readonly class RevokeHandler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private Clock $clock
    ) {
    }

    /**
     * Best-effort revoke. If the token is unknown we still return OK (idempotent logout).
     */
    public function handle(RevokeCommand $command): array
    {
        $stored = $this->refreshTokenRepository->find($command->getRefreshToken());
        if ($stored !== null) {
            $this->refreshTokenRepository->revokeFamily($stored->getFamilyId(), $this->clock->now());
        }

        return ['ok' => true];
    }
}
