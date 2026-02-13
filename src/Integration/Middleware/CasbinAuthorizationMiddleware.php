<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Shared\DomainException;
use App\Domain\Token\TokenVerifier;
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
        private TokenVerifier $tokenVerifier,
        private string $defaultScope = 'api',
        private string $guestApiKey = '',
        private string $unauthorizedMessage = 'Unauthorized',
        private string $forbiddenMessage = 'Forbidden'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $this->withCorsHeaders($this->responseFactory->createResponse(200));
        }

        if ($this->hasNoPolicies()) {
            return $handler->handle($request);
        }

        $authContext = $this->resolveSubjects($request);
        if ($authContext['error'] !== null) {
            return $this->respondWithError(401, $authContext['error']);
        }

        $subjects = $authContext['subjects'];
        if ($subjects === []) {
            return $this->respondWithError(401, $this->unauthorizedMessage);
        }

        $scope = $this->resolveScope($request);
        $object = $this->resolveObject($request);
        $action = strtoupper($request->getMethod());

        foreach ($subjects as $subject) {
            if ($this->enforcer->enforce($subject, $object, $action, $scope)) {
                return $handler->handle($request->withAttribute('auth.subject', $subject));
            }
        }

        return $this->respondWithError(403, $this->forbiddenMessage);
    }

    /**
     * @return array{subjects:string[],error:?string}
     */
    private function resolveSubjects(ServerRequestInterface $request): array
    {
        $subject = trim($request->getHeaderLine('X-Subject'));
        if ($subject !== '') {
            return ['subjects' => [$subject], 'error' => null];
        }

        $authorization = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
            $token = trim($matches[1]);
            if ($token === '') {
                return ['subjects' => [], 'error' => $this->unauthorizedMessage];
            }

            try {
                $identity = $this->tokenVerifier->verify($token);
            } catch (DomainException) {
                return ['subjects' => [], 'error' => $this->unauthorizedMessage];
            }

            $roles = array_values(array_unique(array_map(
                static fn(string $role): string => strtolower(trim($role)),
                $identity->getRoles()
            )));

            if ($roles !== []) {
                return ['subjects' => $roles, 'error' => null];
            }

            return ['subjects' => [sprintf('user:%s', $identity->getUserId())], 'error' => null];
        }

        $clientId = trim($request->getHeaderLine('X-Client-Id'));
        if ($clientId !== '') {
            return ['subjects' => [$clientId], 'error' => null];
        }

        $providedApiKey = trim($request->getHeaderLine('X-API-Key'));
        if ($providedApiKey !== '' && $this->guestApiKey !== '' && hash_equals($this->guestApiKey, $providedApiKey)) {
            return ['subjects' => ['guest'], 'error' => null];
        }

        return ['subjects' => [], 'error' => null];
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

    private function hasNoPolicies(): bool
    {
        return $this->enforcer->getPolicy() === []
            && $this->enforcer->getGroupingPolicy() === [];
    }

    private function withCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope, X-API-Key');
    }
}
