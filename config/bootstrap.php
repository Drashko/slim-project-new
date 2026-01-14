<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

$container = $containerBuilder->build();

/** @var App $app */
$app = $container->get(App::class);

$settings = (array) $container->get('settings');
$routeCache = (array) ($settings['route_cache'] ?? []);
if (!empty($routeCache['enabled']) && !empty($routeCache['path'])) {
    $cachePath = (string) $routeCache['path'];
    $cacheDir = dirname($cachePath);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0775, true);
    }
    $app->getRouteCollector()->setCacheFile($cachePath);
}

(require __DIR__ . '/routes.php')($app);
(require __DIR__ . '/middleware.php')($app);

return $app;
