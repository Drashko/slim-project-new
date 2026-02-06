<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Public;

use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeEndpoint
{
    use JsonResponseTrait;

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->respondWithJson($response, [
            'status' => 'ok',
            'message' => 'API is running',
        ]);
    }
}
