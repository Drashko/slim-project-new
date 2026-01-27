<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Rbac\Policy;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminManageUsersPermissionMiddleware implements MiddlewareInterface
{
    use LocalizedRouteTrait;

    private const REQUIRED_ABILITY = 'admin.users.manage';

    public function __construct(
        private readonly AdminAuthenticator $authenticator,
        private readonly Policy $policy,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->authenticator->authenticate($request);
        $roles = $this->resolveRoles($user['roles'] ?? []);

        if ($this->policy->isGranted($roles, self::REQUIRED_ABILITY, $this->resolveRolesVersion($user))) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        $response = str_starts_with($path, '/api')
            ? $this->responseFactory->createResponse(403)
            : $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->localizedPath($request, 'admin'));

        if (str_starts_with($path, '/api')) {
            $response->getBody()->write(json_encode(['error' => 'Forbidden']));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $response;
    }

    /**
     * @param mixed $roles
     * @return string[]
     */
    private function resolveRoles(mixed $roles): array
    {
        if (!is_array($roles)) {
            return [(string) $roles];
        }

        return array_values(array_map(static fn(mixed $role): string => (string) $role, $roles));
    }

    /**
     * @param array<string, mixed> $user
     */
    private function resolveRolesVersion(array $user): ?int
    {
        $version = $user['roles_version'] ?? $user['rolesVersion'] ?? null;
        if (is_numeric($version)) {
            return (int) $version;
        }

        return null;
    }
}
