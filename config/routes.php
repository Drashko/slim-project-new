<?php

declare(strict_types=1);

use App\API\Endpoint\V1\User\CreateUserEndpoint;
use App\API\Endpoint\V1\User\GetUserEndpoint;
use App\API\Endpoint\V1\User\ListUsersEndpoint;
use App\Integration\Middleware\CasbinAuthorizationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope');
    });

    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->group('/v1', function (RouteCollectorProxy $versionGroup): void {
            $versionGroup->post('/users', [CreateUserEndpoint::class, 'create'])->setName('api.v1.create-user');//create user
            $versionGroup->get('/users', [ListUsersEndpoint::class, 'list'])->setName('api.v1.get-user-list');///get all users
            $versionGroup->get('/users/{id}', [GetUserEndpoint::class, 'index'])->setName('api.v1.get-user');//get one user by id
        })->add(CasbinAuthorizationMiddleware::class);
    });
};

