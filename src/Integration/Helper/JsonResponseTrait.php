<?php

declare(strict_types=1);

namespace App\Integration\Helper;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

trait JsonResponseTrait
{
    /**
     * @param array<string, mixed>|array<int, mixed> $payload
     * @throws \JsonException
     */
    private function respondWithJson(ResponseInterface $response, array $payload, int $status = 200): ResponseInterface
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Unable to encode JSON response.');
        }

        $response->getBody()->write($json);

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}

