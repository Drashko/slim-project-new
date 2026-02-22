<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Shared\DomainException;
use App\Domain\Token\TokenVerifier;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TokenVerifier $tokenVerifier,
        private ResponseFactoryInterface $responseFactory,
        private string $unauthorizedMessage = 'Unauthorized'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return $this->json(401, ['status' => 'error', 'message' => $this->unauthorizedMessage]);
        }

        $token = trim((string) ($matches[1] ?? ''));
        if ($token === '') {
            return $this->json(401, ['status' => 'error', 'message' => $this->unauthorizedMessage]);
        }

        try {
            $identity = $this->tokenVerifier->verify($token);
        } catch (DomainException) {
            return $this->json(401, ['status' => 'error', 'message' => $this->unauthorizedMessage]);
        }

        return $handler->handle($request->withAttribute('auth.identity', $identity));
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function json(int $status, array $payload): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write((string) (json_encode($payload, JSON_UNESCAPED_SLASHES) ?: ''));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
