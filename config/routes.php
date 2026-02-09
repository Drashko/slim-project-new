<?php

declare(strict_types=1);

use App\API\Endpoint\V1\Admin\HomeAdminEndpoint;
use App\API\Endpoint\V1\Admin\PermissionEndpoint;
use App\API\Endpoint\V1\Public\HomeEndpoint;
use App\Integration\Middleware\CasbinAuthorizationMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope');
    });

    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->group('/v1', function (RouteCollectorProxy $versionGroup): void {
            $versionGroup->get('', [HomeEndpoint::class, 'index'])->setName('api.v1.home');
            $versionGroup->get('/admin', [HomeAdminEndpoint::class, 'index'])->setName('api.v1.admin.home');
            $versionGroup->get('/admin/permissions', [PermissionEndpoint::class, 'index'])->setName('api.v1.admin.permissions');
            $versionGroup->post('/admin/permissions', [PermissionEndpoint::class, 'create'])->setName('api.v1.admin.permissions.create');
        })->add(CasbinAuthorizationMiddleware::class);
    });
};
