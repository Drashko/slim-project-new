<?php

declare(strict_types=1);

namespace App\Web\API\Controller;

use App\Integration\Helper\JsonResponseTrait;
use App\Integration\Session\PublicSessionInterface;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ApiIndexController
{
    use JsonResponseTrait;

    public function __construct(
        private PublicSessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = PublicUserResolver::resolve($this->session->get('user'));

        return $this->respondWithJson($response, [
            'route' => 'api.index',
            'user' => $user,
        ]);
    }
}
