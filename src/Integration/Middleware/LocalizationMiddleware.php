<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LocalizationMiddleware implements MiddlewareInterface
{
    private const COOKIE_LIFETIME = 60 * 60 * 24 * 365;

    /**
     * @param array<string, string> $supportedLocales
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SessionInterface $session,
        private readonly array $supportedLocales,
        private readonly string $defaultLocale
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = $this->determineContext($request);
        $sessionKey = $this->sessionKeyForContext($context);
        $cookieName = $this->cookieNameForContext($context);
        $locale = $this->resolveLocale($request, $sessionKey, $cookieName);

        $this->session->set($sessionKey, $locale);
        $this->translator->setLocale($locale);
        $this->translator->setFallbackLocales([$this->defaultLocale]);

        $response = $handler->handle(
            $request
                ->withAttribute('locale', $locale)
                ->withAttribute('locale_scope', $context)
        );

        return $this->addLocaleCookie($response, $cookieName, $locale);
    }

    private function resolveLocale(ServerRequestInterface $request, string $sessionKey, string $cookieName): string
    {
        $pathLocale = $this->extractLocaleFromPath($request);
        if ($pathLocale !== null) {
            return $pathLocale;
        }

        $queryLocale = $this->sanitizeLocale($request->getQueryParams()['lang'] ?? null);
        if ($queryLocale !== null) {
            return $queryLocale;
        }

        $sessionLocale = $this->sanitizeLocale($this->session->get($sessionKey));
        if ($sessionLocale !== null) {
            return $sessionLocale;
        }

        $cookieLocale = $this->sanitizeLocale($request->getCookieParams()[$cookieName] ?? null);
        if ($cookieLocale !== null) {
            return $cookieLocale;
        }

        $headerLocale = $this->negotiateFromHeader($request->getHeaderLine('Accept-Language'));
        if ($headerLocale !== null) {
            return $headerLocale;
        }

        return $this->defaultLocale;
    }

    private function sanitizeLocale(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $normalized = strtolower(str_replace('_', '-', $value));

        if (array_key_exists($normalized, $this->supportedLocales)) {
            return $normalized;
        }

        $short = substr($normalized, 0, 2);
        if ($short !== '' && array_key_exists($short, $this->supportedLocales)) {
            return $short;
        }

        return null;
    }

    private function negotiateFromHeader(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        $candidates = explode(',', $header);
        foreach ($candidates as $candidate) {
            $locale = trim($candidate);
            if ($locale === '') {
                continue;
            }

            $parts = explode(';', $locale);
            $primary = $this->sanitizeLocale($parts[0] ?? '');
            if ($primary !== null) {
                return $primary;
            }
        }

        return null;
    }

    private function determineContext(ServerRequestInterface $request): string
    {
        $segments = $this->pathSegments($request);

        if ($segments !== []) {
            $first = $this->sanitizeLocale($segments[0]);
            if ($first !== null) {
                array_shift($segments);
            }
        }

        if ($segments !== [] && $this->isAdminSegments($segments)) {
            return 'admin';
        }

        return 'public';
    }

    /**
     * @param list<string> $segments
     */
    private function isAdminSegments(array $segments): bool
    {
        if ($segments === []) {
            return false;
        }

        if ($segments[0] === 'admin') {
            return true;
        }

        return false;
    }

    private function sessionKeyForContext(string $context): string
    {
        return match ($context) {
            'admin' => 'locale_admin',
            default => 'locale_public',
        };
    }

    private function cookieNameForContext(string $context): string
    {
        return $this->sessionKeyForContext($context);
    }

    private function extractLocaleFromPath(ServerRequestInterface $request): ?string
    {
        $segments = $this->pathSegments($request);
        if ($segments === []) {
            return null;
        }

        return $this->sanitizeLocale($segments[0]);
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

        $segments = array_values(array_filter(
            explode('/', $path),
            static fn(string $segment): bool => $segment !== ''
        ));

        return array_map(static fn(string $segment): string => strtolower($segment), $segments);
    }

    private function addLocaleCookie(ResponseInterface $response, string $cookieName, string $locale): ResponseInterface
    {
        $expiresAt = time() + self::COOKIE_LIFETIME;
        $cookieParts = [
            sprintf('%s=%s', rawurlencode($cookieName), rawurlencode($locale)),
            'Path=/',
            'Max-Age=' . self::COOKIE_LIFETIME,
            'Expires=' . gmdate('D, d-M-Y H:i:s T', $expiresAt),
            'SameSite=Lax',
        ];

        return $response->withAddedHeader('Set-Cookie', implode('; ', $cookieParts));
    }
}
