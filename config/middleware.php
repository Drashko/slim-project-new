<?php

declare(strict_types=1);

use App\Integration\Middleware\LocalizationMiddleware;
use App\Integration\Middleware\StaticAssetCacheMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->add(function ($request, $handler) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $path = $request->getUri()->getPath();
            $segments = array_values(array_filter(
                explode('/', trim((string) $path, '/')),
                static fn(string $segment): bool => $segment !== ''
            ));
            $isAdminPath = $segments !== [] && strtolower($segments[0]) === 'admin';

            $params = session_get_cookie_params();
            if ($isAdminPath) {
                session_name('ADMINPHPSESSID');
                $params['path'] = '/admin';
            } else {
                session_name('PHPSESSID');
                $params['path'] = '/';
            }
            session_set_cookie_params($params);

            session_start();
        }

        return $handler->handle($request);
    });
    $app->add(LocalizationMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(StaticAssetCacheMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
