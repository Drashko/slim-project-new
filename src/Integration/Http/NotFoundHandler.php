<?php

declare(strict_types=1);

namespace App\Integration\Http;

use App\Integration\View\TemplateRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class NotFoundHandler
{
    /**
     * @param array<string, string> $supportedLocales
     */
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly array $supportedLocales = [],
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse(404);
        $scope = $this->resolveScope($request);
        $template = $scope === 'admin' ? 'admin::errors/404' : 'errors/404';

        return $this->renderer->render($response, $template, [
            'requestedPath' => $request->getUri()->getPath(),
        ]);
    }

    private function resolveScope(ServerRequestInterface $request): string
    {
        $scope = $request->getAttribute('locale_scope');
        if (is_string($scope) && in_array($scope, ['admin', 'home'], true)) {
            return $scope;
        }

        $segments = $this->pathSegments($request);
        if ($segments === []) {
            return 'home';
        }

        $first = strtolower($segments[0]);
        if ($this->isSupportedLocale($first)) {
            array_shift($segments);
        }

        if ($segments !== [] && strtolower($segments[0]) === 'admin') {
            return 'admin';
        }

        return 'home';
    }

    private function isSupportedLocale(string $segment): bool
    {
        return array_key_exists($segment, $this->supportedLocales);
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

        return array_values(array_filter(
            array_map(
                static fn(string $segment): string => strtolower($segment),
                explode('/', $path)
            ),
            static fn(string $segment): bool => $segment !== ''
        ));
    }
}
