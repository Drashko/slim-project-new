<?php

declare(strict_types=1);

use App\Integration\Middleware\LocalizationMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->add(SessionStartMiddleware::class);
    $app->add(LocalizationMiddleware::class);
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(ErrorMiddleware::class);
};
