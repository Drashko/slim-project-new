<?php

declare(strict_types=1);

namespace App\Web\Admin\Middleware;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Web\Auth\Dto\RegisterFormData;
use App\Web\Shared\LocalizedRouteTrait;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminAuthenticationMiddleware implements MiddlewareInterface
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly AdminAuthenticator $authenticator,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly SessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->session->get('user');
        if (is_array($user)) {
            $roles = $this->normalizeRoles($user['roles'] ?? []);
            if ($this->isPublicOnly($roles)) {
                $response = $this->responseFactory->createResponse(302);

                return $response->withHeader('Location', $this->localizedPath($request, 'auth/login'));
            }
        }

        try {
            $this->authenticator->authenticate($request);
        } catch (DomainException) {
            $response = $this->responseFactory->createResponse(302);

            return $response->withHeader('Location', $this->localizedPath($request, 'admin/login'));
        }

        return $handler->handle($request);
    }

    /**
     * @return string[]
     */
    private function normalizeRoles(mixed $roles): array
    {
        if ($roles === null) {
            return [];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $normalized = [];
        foreach ($roles as $role) {
            if (is_scalar($role)) {
                $normalized[] = trim((string) $role);
            }
        }

        return array_values(array_filter($normalized, static fn(string $role): bool => $role !== ''));
    }

    /**
     * @param string[] $roles
     */
    private function isPublicOnly(array $roles): bool
    {
        return in_array(RegisterFormData::ROLE_USER, $roles, true)
            && !in_array(RegisterFormData::ROLE_ADMIN, $roles, true);
    }
}
