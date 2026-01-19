<?php

declare(strict_types=1);

use App\Integration\Middleware\LocalizationMiddleware;
use App\Integration\Middleware\StaticAssetCacheMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->add(LocalizationMiddleware::class);
    $app->add(SessionStartMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(StaticAssetCacheMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
