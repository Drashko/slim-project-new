<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Admin;

use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeAdminEndpoint
{
    use JsonResponseTrait;

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->respondWithJson($response, [
            'status' => 'ok',
            'message' => 'Admin API is running',
        ]);
    }
}
