<?php

declare(strict_types=1);

use App\API\Endpoint\Auth\LoginEndpoint;
use App\API\Endpoint\Auth\LogoutEndpoint;
use App\API\Endpoint\Auth\RefreshEndpoint;
use App\API\Endpoint\V1\Admin\Users\ListUsersAdminEndpoint;
use App\API\Endpoint\V1\Users\CreateUserEndpoint;
use App\API\Endpoint\V1\Users\DeleteUserEndpoint;
use App\API\Endpoint\V1\Users\GetUserEndpoint;
use App\API\Endpoint\V1\Users\ListUsersEndpoint;
use App\API\Endpoint\V1\Users\UpdateUserEndpoint;
use App\API\Endpoint\V1\Ме\MeEndpoint;
use App\Integration\Middleware\CasbinAuthorizationMiddleware;
use App\Integration\Middleware\InternalSignatureMiddleware;
use App\Integration\Middleware\JwtAuthMiddleware;
use App\Integration\Middleware\LoginRateLimitMiddleware;
use App\Integration\Middleware\RefreshRateLimitMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $allowedOrigins = array_values(array_filter(array_map(
        static fn(string $origin): string => trim($origin),
        explode(',', (string) ($_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000'))
    ), static fn(string $origin): bool => $origin !== ''));

    $app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response) use ($allowedOrigins): ResponseInterface {
        $origin = trim($request->getHeaderLine('Origin'));
        $allowOrigin = in_array($origin, $allowedOrigins, true) ? $origin : (string) ($allowedOrigins[0] ?? 'http://localhost:3000');

        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowOrigin)
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    });

    // Public auth endpoints for non-browser clients (mobile/S2S). Next BFF uses the internal equivalents.
    $app->group('/auth', function (RouteCollectorProxy $auth): void {
        $auth->post('/login', LoginEndpoint::class)
            ->add(LoginRateLimitMiddleware::class)
            ->setName('auth.login');
        $auth->post('/refresh', RefreshEndpoint::class)
            ->add(RefreshRateLimitMiddleware::class)
            ->setName('auth.refresh');
        $auth->post('/logout', LogoutEndpoint::class)->setName('auth.logout');
    });

    // Public REST API for external clients (JWT-only).
    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->group('/v1', function (RouteCollectorProxy $versionGroup): void {
            $versionGroup->get('/me', MeEndpoint::class)->setName('api.v1.me');
            $versionGroup->post('/users', [CreateUserEndpoint::class, 'create'])->setName('api.v1.create-user');
            $versionGroup->delete('/users/{id}', [DeleteUserEndpoint::class, 'delete'])->setName('api.v1.delete-user');
            $versionGroup->get('/users', [ListUsersEndpoint::class, 'list'])->setName('api.v1.get-user-list');
            $versionGroup->get('/users/{id}', [GetUserEndpoint::class, 'index'])->setName('api.v1.get-user');
            $versionGroup->put('/users/{id}', [UpdateUserEndpoint::class, 'update'])->setName('api.v1.update-user');
            //admin section
            $versionGroup->get('/admin/users', [ListUsersAdminEndpoint::class])->setName('api.v1.admin.create-user');

        })
            ->add(CasbinAuthorizationMiddleware::class)
            ->add(JwtAuthMiddleware::class);
    });

    // Internal endpoints callable only from the Next.js BFF (HMAC + same handlers).
    $app->group('/_internal', function (RouteCollectorProxy $internal): void {
        $internal->group('/auth', function (RouteCollectorProxy $auth): void {
            $auth->post('/login', LoginEndpoint::class)
                ->add(LoginRateLimitMiddleware::class)
                ->setName('internal.auth.login');
            $auth->post('/refresh', RefreshEndpoint::class)
                ->add(RefreshRateLimitMiddleware::class)
                ->setName('internal.auth.refresh');
            $auth->post('/logout', LogoutEndpoint::class)->setName('internal.auth.logout');
        });

        $internal->group('/api', function (RouteCollectorProxy $api): void {
            $api->group('/v1', function (RouteCollectorProxy $versionGroup): void {
                $versionGroup->get('/me', MeEndpoint::class)->setName('internal.api.v1.me');
                $versionGroup->post('/users', [CreateUserEndpoint::class, 'create'])->setName('internal.api.v1.create-user');
                $versionGroup->delete('/users/{id}', [DeleteUserEndpoint::class, 'delete'])->setName('internal.api.v1.delete-user');
                $versionGroup->get('/users', [ListUsersEndpoint::class, 'list'])->setName('internal.api.v1.get-user-list');
                $versionGroup->get('/users/{id}', [GetUserEndpoint::class, 'index'])->setName('internal.api.v1.get-user');
                $versionGroup->put('/users/{id}', [UpdateUserEndpoint::class, 'update'])->setName('internal.api.v1.update-user');
            })
                ->add(CasbinAuthorizationMiddleware::class)
                ->add(JwtAuthMiddleware::class);
        });
    })->add(InternalSignatureMiddleware::class);
};
