<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\StreamFactory;

final class StaticAssetCacheMiddleware implements MiddlewareInterface
{
    private const CACHE_TTL = 604800;
    private const CACHEABLE_EXTENSIONS = [
        'css',
        'js',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'webp',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $path = $request->getUri()->getPath();
        if (!$this->isCacheable($path)) {
            return $response;
        }

        $response = $response
            ->withHeader('Cache-Control', 'public, max-age=' . self::CACHE_TTL)
            ->withHeader('Vary', 'Accept-Encoding');

        return $this->maybeGzipResponse($request, $response);
    }

    private function isCacheable(string $path): bool
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::CACHEABLE_EXTENSIONS, true);
    }

    private function maybeGzipResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $acceptEncoding = $request->getHeaderLine('Accept-Encoding');
        if (stripos($acceptEncoding, 'gzip') === false) {
            return $response;
        }

        if ($response->hasHeader('Content-Encoding')) {
            return $response;
        }

        if (!str_starts_with((string) $response->getHeaderLine('Content-Type'), 'text/')
            && !str_contains((string) $response->getHeaderLine('Content-Type'), 'javascript')
            && !str_contains((string) $response->getHeaderLine('Content-Type'), 'json')
        ) {
            return $response;
        }

        $bodyContents = (string) $response->getBody();
        if ($bodyContents === '') {
            return $response;
        }

        $compressed = gzencode($bodyContents, 6);
        if ($compressed === false) {
            return $response;
        }

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStream($compressed);

        return $response
            ->withHeader('Content-Encoding', 'gzip')
            ->withHeader('Content-Length', (string) strlen($compressed))
            ->withBody($stream);
    }
}
