<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class IndexController
{
    use JsonResponseTrait;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->respondWithJson($response, [
            'route' => 'front.home',
            'user' => null,
        ]);
    }
}
