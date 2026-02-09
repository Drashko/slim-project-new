<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Casbin\Enforcer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

final readonly class CasbinAuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Enforcer $enforcer,
        private ResponseFactoryInterface $responseFactory,
        private string $defaultScope = 'api',
        private string $unauthorizedMessage = 'Unauthorized',
        private string $forbiddenMessage = 'Forbidden'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $this->withCorsHeaders($this->responseFactory->createResponse(200));
        }

        $subject = $this->resolveSubject($request);
        $scope = $this->resolveScope($request);
        $object = $this->resolveObject($request);
        $action = strtoupper($request->getMethod());

        if ($subject === null) {
            return $this->respondWithError(401, $this->unauthorizedMessage);
        }

        if ($this->enforcer->enforce($subject, $object, $action, $scope)) {
            return $handler->handle($request);
        }

        return $this->respondWithError(403, $this->forbiddenMessage);
    }

    private function resolveSubject(ServerRequestInterface $request): ?string
    {
        $subject = trim($request->getHeaderLine('X-Subject'));
        if ($subject !== '') {
            return $subject;
        }

        $clientId = trim($request->getHeaderLine('X-Client-Id'));
        if ($clientId !== '') {
            return $clientId;
        }

        $authorization = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            $tokenSubject = trim($matches[1]);
            if ($tokenSubject !== '') {
                return $tokenSubject;
            }
        }

        return 'anonymous';
    }

    private function resolveScope(ServerRequestInterface $request): string
    {
        $scope = trim($request->getHeaderLine('X-Scope'));

        return $scope !== '' ? $scope : $this->defaultScope;
    }

    private function resolveObject(ServerRequestInterface $request): string
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        return $route ? $route->getPattern() : $request->getUri()->getPath();
    }

    private function respondWithError(int $status, string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_UNESCAPED_SLASHES) ?: '');

        return $this->withCorsHeaders($response)->withHeader('Content-Type', 'application/json');
    }

    private function withCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope');
    }
}
