<?php

declare(strict_types=1);

use App\Integration\Middleware\StaticAssetCacheMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(StaticAssetCacheMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
