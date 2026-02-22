<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Ме;

use App\Domain\Token\Identity;
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Returns the currently authenticated identity ("who am I").
 *
 * This endpoint is designed to be consumed via the Next.js BFF.
 */
final readonly class MeEndpoint
{
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Identity|null $identity */
        $identity = $request->getAttribute('auth.identity');
        if ($identity === null) {
            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write((string) (json_encode([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?: ''));

            return $response->withHeader('Content-Type', 'application/json');
        }

        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write((string) (json_encode([
            'status' => 'ok',
            'data' => $identity->toArray(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) ?: ''));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
