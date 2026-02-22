<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Integration\Middleware\StaticAssetCacheMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $allowedOrigins = array_values(array_filter(array_map(
        static fn(string $origin): string => trim($origin),
        explode(',', (string) ($_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000'))
    ), static fn(string $origin): bool => $origin !== ''));
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(ErrorMiddleware::class);

    $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($allowedOrigins) {
        $response = $handler->handle($request);
        $origin = trim($request->getHeaderLine('Origin'));
        $allowOrigin = in_array($origin, $allowedOrigins, true) ? $origin : (string) ($allowedOrigins[0] ?? 'http://localhost:3000');

        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowOrigin)
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    });
};
