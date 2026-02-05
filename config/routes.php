<?php

declare(strict_types=1);

use App\API\Controller\LocalizationController;
use App\Web\Admin\Controller\HomeController;
use App\Web\Public\Controller\IndexController as FrontController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $container = $app->getContainer();
    $supportedLocales = ['en' => 'English', 'bg' => 'Български'];
    $defaultLocale = 'en';

    if ($container !== null && $container->has('settings')) {
        $settings = (array) $container->get('settings');
        $localization = (array) ($settings['localization'] ?? []);

        $supportedLocales = (array) ($localization['supported_locales'] ?? $supportedLocales);
        $defaultLocale = (string) ($localization['default_locale'] ?? $defaultLocale);
    }

    if (!array_key_exists($defaultLocale, $supportedLocales)) {
        $defaultLocale = array_key_first($supportedLocales) ?? 'en';
    }

    $localePattern = implode('|', array_map(static fn(string $locale): string => preg_quote($locale, '#'), array_keys($supportedLocales)));
    $localeGroupPrefix = sprintf('/{locale:(?:%s)}', $localePattern !== '' ? $localePattern : 'en');

    $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    });

    $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($container, $defaultLocale, $supportedLocales): ResponseInterface {
        $locale = $defaultLocale;
        $cookieLocale = $request->getCookieParams()['locale_public'] ?? null;
        if (is_string($cookieLocale) && $cookieLocale !== '') {
            $normalized = strtolower(str_replace('_', '-', $cookieLocale));
            if (array_key_exists($normalized, $supportedLocales)) {
                $locale = $normalized;
            }
        }

        return $response->withHeader('Location', '/' . $locale)->withStatus(302);
    });

    //home localized
    $app->group($localeGroupPrefix, function (RouteCollectorProxy $group): void {
        $group->get('', FrontController::class)->setName('home.home');
    });

    $app->group('/admin', function (RouteCollectorProxy $adminGroup): void {
        $adminGroup->get('', HomeController::class)->setName('admin.home');
    });

    //api routes
    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('/localization/{locale}', LocalizationController::class);
    });
};
