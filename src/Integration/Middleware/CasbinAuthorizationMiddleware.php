<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Token\Identity;
use Casbin\Enforcer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/**
 * Casbin authorization middleware (deny-by-default).
 *
 * Requires JwtAuthMiddleware to have set 'auth.identity'.
 */
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
        /** @var Identity|null $identity */
        $identity = $request->getAttribute('auth.identity');
        if ($identity === null) {
            return $this->json(401, $this->unauthorizedMessage);
        }

        $subjects = $this->subjectsFromIdentity($identity);
        $scope = $this->defaultScope;
        $object = $this->resolveObject($request);
        $action = strtoupper($request->getMethod());

        foreach ($subjects as $subject) {
            if ($this->enforcer->enforce($subject, $object, $action, $scope)) {
                return $handler->handle($request->withAttribute('auth.subject', $subject));
            }
        }

        return $this->json(403, $this->forbiddenMessage);
    }

    /**
     * @return string[]
     */
    private function subjectsFromIdentity(Identity $identity): array
    {
        $roles = array_values(array_unique(array_map(
            static fn(string $role): string => 'role:' . strtolower(trim($role)),
            $identity->getRoles()
        )));

        return array_merge([
            'user:' . $identity->getUserId(),
        ], $roles);
    }

    private function resolveObject(ServerRequestInterface $request): string
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        return $route ? $route->getPattern() : $request->getUri()->getPath();
    }

    private function json(int $status, string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write((string) (json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_UNESCAPED_SLASHES) ?: ''));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
