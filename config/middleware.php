<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Integration\Middleware\StaticAssetCacheMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(StaticAssetCacheMiddleware::class);
    $app->add(ErrorMiddleware::class);

    $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope, X-API-Key');
    });
};
