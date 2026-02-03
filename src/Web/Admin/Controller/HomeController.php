<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HomeController
{
    use JsonResponseTrait;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->respondWithJson($response, [
            'route' => 'admin.home',
            'user' => null,
        ]);
    }
}
