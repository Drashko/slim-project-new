<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\User;

use App\Domain\User\UserInterface;
use App\Feature\Admin\User\Handler\GetUserHandler;
use App\Feature\Admin\User\Query\GetUserQuery;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class GetUserEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly GetUserHandler $getUserHandler)
    {
    }

    /**
     * @param array{id?: string} $args
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        unset($request);

        $userId = trim((string) ($args['id'] ?? ''));
        if ($userId === '') {
            return $this->respondWithJson($response, ['message' => 'User id is required.'], 400);
        }

        try {
            $user = $this->getUserHandler->handle(new GetUserQuery($userId));
            if (!$user instanceof UserInterface) {
                return $this->respondWithJson($response, ['message' => 'User not found.'], 404);
            }

            return $this->respondWithJson($response, ['user' => $this->transformUser($user)]);
        } catch (Throwable) {
            return $this->respondWithJson($response, ['message' => 'Unable to get user.'], 500);
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
