<?php

declare(strict_types=1);

namespace App\Web\Shared\Middleware;

use App\Web\Shared\LocalizedRouteTrait;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PublicAreaRoleRedirectMiddleware implements MiddlewareInterface
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly SessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isAdminRequest($request)) {
            return $handler->handle($request);
        }

        $user = $this->session->get('user');
        if (!is_array($user)) {
            return $handler->handle($request);
        }

        return $handler->handle($request);
    }

    private function isAdminRequest(ServerRequestInterface $request): bool
    {
        $segments = $this->pathSegments($request);
        if ($segments !== [] && $this->isLocaleSegment($segments[0])) {
            array_shift($segments);
        }

        return $segments !== [] && $segments[0] === 'admin';
    }

    /**
     * @return list<string>
     */
    private function pathSegments(ServerRequestInterface $request): array
    {
        $path = trim($request->getUri()->getPath(), '/');
        if ($path === '') {
            return [];
        }

        return array_values(array_map('strtolower', array_filter(
            explode('/', $path),
            static fn(string $segment): bool => $segment !== ''
        )));
    }

    private function isLocaleSegment(string $segment): bool
    {
        return (bool) preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/i', $segment);
    }
}
