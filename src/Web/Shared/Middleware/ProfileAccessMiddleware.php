<?php

declare(strict_types=1);

namespace App\Web\Shared\Middleware;

use App\Integration\Session\PublicSessionInterface;
use App\Web\Front\Dto\RegisterFormData;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProfileAccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly PublicSessionInterface $session,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->session->get('user');
        if (!is_array($user)) {
            return $handler->handle($request);
        }

        $roles = $this->normalizeRoles($user['roles'] ?? []);
        if ($this->hasAdminRole($roles)) {
            $response = $this->responseFactory->createResponse(403);
            $response->getBody()->write($this->translator->trans('auth.login.flash.access_denied'));

            return $response;
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
    private function hasAdminRole(array $roles): bool
    {
        return in_array(RegisterFormData::ROLE_ADMIN, $roles, true);
    }
}
