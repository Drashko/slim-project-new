<?php

declare(strict_types=1);

use App\Web\Admin\Controller\AuditLog\AuditLogController;
use App\Web\Admin\Controller\Auth\LoginController as AdminLoginController;
use App\Web\Admin\Controller\Category\CategoryManagementController;
use App\Web\Admin\Controller\Home\HomeController;
use App\Web\Admin\Controller\Permission\PermissionMatrixController;
use App\Web\Admin\Controller\Profile\ProfileController as AdminProfileController;
use App\Web\Admin\Controller\Role\RoleCreateController;
use App\Web\Admin\Controller\Role\RoleManagementController;
use App\Web\Admin\Controller\User\UserCreateController;
use App\Web\Admin\Controller\User\UserDetailController;
use App\Web\Admin\Controller\User\UserManagementController;
use App\Web\Admin\Controller\Ad\AdDetailController;
use App\Web\Admin\Controller\Ad\AdManagementController;
use App\Web\Admin\Middleware\AdminAuthenticationMiddleware;
use App\Web\Shared\Middleware\PublicAreaRoleRedirectMiddleware;
use App\Web\Shared\Middleware\ProfileAccessMiddleware;
use App\Web\API\Controller\AdminOverviewController;
use App\Web\API\Controller\LocalizationController;
use App\Web\Api\Controller\ApiIndexController;
use App\Web\Auth\LoginController;
use App\Web\Auth\LogoutController;
use App\Web\Auth\RegisterController;
use App\Web\Front\Controller\IndexController as FrontController;
use App\Web\Profile\ProfileController;
use App\Web\Profile\AdsController;
use Odan\Session\SessionInterface;
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
        if ($container !== null && $container->has(SessionInterface::class)) {
            /** @var SessionInterface $session */
            $session = $container->get(SessionInterface::class);
            $storedLocale = $session->get('locale_public');
            if (is_string($storedLocale) && $storedLocale !== '') {
                $locale = $storedLocale;
            }
        }

        return $response->withHeader('Location', '/' . $locale)->withStatus(302);
    });

    $app->get('/admin', function (ServerRequestInterface $request, ResponseInterface $response) use ($container, $defaultLocale): ResponseInterface {
        $locale = $defaultLocale;
        if ($container !== null && $container->has(SessionInterface::class)) {
            /** @var SessionInterface $session */
            $session = $container->get(SessionInterface::class);
            $storedLocale = $session->get('locale_admin');
            if (!is_string($storedLocale) || $storedLocale === '') {
                $storedLocale = $session->get('locale_public');
            }
            if (is_string($storedLocale) && $storedLocale !== '') {
                $locale = $storedLocale;
            }
        }

        return $response->withHeader('Location', '/' . $locale . '/admin')->withStatus(302);
    });
    //public and admin localized
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

        $group->group('/admin', function (RouteCollectorProxy $adminGroup): void {
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
    })->add(PublicAreaRoleRedirectMiddleware::class);

    //api routes
    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('', ApiIndexController::class);
        $group->get('/admin/overview', AdminOverviewController::class);
        $group->get('/localization/{locale}', LocalizationController::class);
    });
};
