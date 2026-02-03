<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProfileController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private readonly AdminAuthenticator $authenticator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $this->respondWithJson($response, [
                'error' => 'Unauthorized',
                'redirect' => $this->localizedPath($request, 'admin/login'),
            ], 401);
        }

        return $this->respondWithJson($response, [
            'route' => 'admin.profile',
            'user' => $user,
        ]);
    }
}
