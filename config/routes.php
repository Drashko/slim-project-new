<?php

declare(strict_types=1);

use App\Integration\Session\PublicSessionInterface;
use App\Web\Admin\Controller\AdDetailController;
use App\Web\Admin\Controller\AdManagementController;
use App\Web\Admin\Controller\AuditLogController;
use App\Web\Admin\Controller\CategoryManagementController;
use App\Web\Admin\Controller\HomeController;
use App\Web\Admin\Controller\LoginController as AdminLoginController;
use App\Web\Admin\Controller\PermissionMatrixController;
use App\Web\Admin\Controller\ProfileController as AdminProfileController;
use App\Web\Admin\Controller\RoleCreateController;
use App\Web\Admin\Controller\RoleManagementController;
use App\Web\Admin\Controller\UserCreateController;
use App\Web\Admin\Controller\UserDetailController;
use App\Web\Admin\Controller\UserManagementController;
use App\Integration\Middleware\AdminAuthenticationMiddleware;
use App\Web\API\Controller\AdminOverviewController;
use App\Web\Api\Controller\ApiIndexController;
use App\Web\API\Controller\LocalizationController;
use App\Web\Front\Controller\AdsController;
use App\Web\Front\Controller\IndexController as FrontController;
use App\Web\Front\Controller\LoginController;
use App\Web\Front\Controller\LogoutController;
use App\Web\Front\Controller\ProfileController;
use App\Web\Front\Controller\RegisterController;
use App\Web\Shared\Middleware\ProfileAccessMiddleware;
use App\Web\Shared\Middleware\PublicAreaRoleRedirectMiddleware;
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

    $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($container, $defaultLocale): ResponseInterface {
        $locale = $defaultLocale;
        if ($container !== null && $container->has(PublicSessionInterface::class)) {
            /** @var PublicSessionInterface $session */
            $session = $container->get(PublicSessionInterface::class);
            $storedLocale = $session->get('locale_public');
            if (is_string($storedLocale) && $storedLocale !== '') {
                $locale = $storedLocale;
            }
        }

        return $response->withHeader('Location', '/' . $locale)->withStatus(302);
    });

    //public localized
    $app->group($localeGroupPrefix, function (RouteCollectorProxy $group): void {
        $group->get('', FrontController::class)->setName('front.home');

        $group->group('/auth', function (RouteCollectorProxy $authGroup): void {
            $authGroup->map(['GET', 'POST'], '/login', LoginController::class)->setName('auth.login');
            $authGroup->map(['GET', 'POST'], '/register', RegisterController::class)->setName('auth.register');
            $authGroup->get('/logout', LogoutController::class)->setName('auth.logout');
        });

        $group->group('/profile', function (RouteCollectorProxy $profileGroup): void {
            $profileGroup->get('', ProfileController::class)->setName('profile.overview');
            $profileGroup->map(['GET', 'POST'], '/login', LoginController::class)->setName('profile.login');
            $profileGroup->map(['GET', 'POST'], '/ads', AdsController::class)->setName('profile.ads');
        })->add(ProfileAccessMiddleware::class);

        $group->get('/admin', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        });
    })->add(PublicAreaRoleRedirectMiddleware::class);

    $app->group('/admin', function (RouteCollectorProxy $adminGroup): void {
        $adminGroup->map(['GET', 'POST'], '/login', AdminLoginController::class)->setName('admin.login');
        $adminGroup->get('/logout', LogoutController::class)->setName('admin.logout');

        $adminGroup->group('', function (RouteCollectorProxy $protectedGroup): void {
            $protectedGroup->get('/profile', AdminProfileController::class)->setName('admin.profile');
            $protectedGroup->get('/users', UserManagementController::class)->setName('admin.users');
            $protectedGroup->map(['GET', 'POST'], '/users/new', UserCreateController::class)->setName('admin.users.new');
            $protectedGroup->map(['GET', 'POST'], '/users/{id}', UserDetailController::class)->setName('admin.user_detail');
            $protectedGroup->map(['GET', 'POST'], '/roles', RoleManagementController::class)->setName('admin.roles');
            $protectedGroup->map(['GET', 'POST'], '/roles/new', RoleCreateController::class)->setName('admin.roles.new');
            $protectedGroup->map(['GET', 'POST'], '/permissions', PermissionMatrixController::class)
                ->setName('admin.permissions');
            $protectedGroup->map(['GET', 'POST'], '/categories', CategoryManagementController::class)
                ->setName('admin.categories');
            $protectedGroup->get('/ads', AdManagementController::class)->setName('admin.ads');
            $protectedGroup->map(['GET', 'POST'], '/ads/{id}', AdDetailController::class)->setName('admin.ad_detail');
            $protectedGroup->get('/audit', AuditLogController::class)->setName('admin.audit');
            $protectedGroup->get('', HomeController::class);
        })->add(AdminAuthenticationMiddleware::class);
    });

    //api routes
    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('', ApiIndexController::class);
        $group->get('/admin/overview', AdminOverviewController::class);
        $group->get('/localization/{locale}', LocalizationController::class);
    });
};
