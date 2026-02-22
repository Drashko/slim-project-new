<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Users;

use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ListUsersEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        try {
            $users = array_map(
                fn(UserInterface $user): array => $this->transformUser($user),
                $this->userRepository->all()
            );


            return $this->respondWithJson($response, ['users' => $users]);

        } catch (Throwable) {
            return $this->respondWithJson($response, ['message' => 'Unable to list users.'], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformUser(UserInterface $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'status' => $user->getStatus(),
        ];
    }
}
