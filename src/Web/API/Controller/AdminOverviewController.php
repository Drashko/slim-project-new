<?php

declare(strict_types=1);

namespace App\Web\API\Controller;

use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AdminOverviewController
{
    public function __construct(
        private UserRepositoryInterface $users,
        private RoleRepositoryInterface $roles,
        private AdRepositoryInterface $ads,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $payload = [
            'users' => count($this->users->all()),
            'roles' => count($this->roles->all()),
            'ads' => count($this->ads->all()),
        ];

        $response->getBody()->write((string)json_encode($payload, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
