<?php

declare(strict_types=1);

use App\API\Endpoint\V1\User\CreateUserEndpoint;
use App\API\Endpoint\V1\User\DeleteUserEndpoint;
use App\API\Endpoint\V1\User\GetUserEndpoint;
use App\API\Endpoint\V1\User\ListUsersEndpoint;
use App\API\Endpoint\V1\User\UpdateUserEndpoint;
use App\Integration\Middleware\CasbinAuthorizationMiddleware;
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
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Subject, X-Scope, X-API-Key')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    });

    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('/v1/public', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
            unset($request);

            $response->getBody()->write((string) json_encode([
                'status' => 'ok',
                'message' => 'Public API is available.',
            ], JSON_UNESCAPED_UNICODE));

            return $response->withHeader('Content-Type', 'application/json');
        })->setName('api.v1.public');

        $group->group('/v1', function (RouteCollectorProxy $versionGroup): void {
            $versionGroup->get('/public', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                unset($request);

                $response->getBody()->write((string) json_encode([
                    'status' => 'ok',
                    'message' => 'Public API is available.',
                ], JSON_UNESCAPED_UNICODE));

                return $response->withHeader('Content-Type', 'application/json');
            })->setName('api.v1.public');

            $versionGroup->post('/users', [CreateUserEndpoint::class, 'create'])->setName('api.v1.create-user');
            $versionGroup->delete('/users/{id}', [DeleteUserEndpoint::class, 'delete'])->setName('api.v1.delete-user');
            $versionGroup->get('/users', [ListUsersEndpoint::class, 'list'])->setName('api.v1.get-user-list');
            $versionGroup->get('/users/{id}', [GetUserEndpoint::class, 'index'])->setName('api.v1.get-user');
            $versionGroup->put('/users/{id}', [UpdateUserEndpoint::class, 'update'])->setName('api.v1.update-user');
        })->add(CasbinAuthorizationMiddleware::class);
    });
};
