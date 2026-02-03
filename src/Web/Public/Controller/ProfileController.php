<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Integration\Helper\JsonResponseTrait;
use App\Integration\Session\PublicSessionInterface;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ProfileController
{
    use JsonResponseTrait;

    public function __construct(
        private PublicSessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $normalizedUser = PublicUserResolver::resolve($this->session->get('user'));

        return $this->respondWithJson($response, [
            'route' => 'profile.overview',
            'user' => $normalizedUser,
        ]);
    }
}
