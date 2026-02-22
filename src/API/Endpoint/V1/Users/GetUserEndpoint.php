<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Users;

use App\Domain\User\UserInterface;
use App\Domain\Token\Identity;
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
        /** @var Identity|null $identity */
        $identity = $request->getAttribute('auth.identity');
        if ($identity === null) {
            return $this->respondWithJson($response, ['message' => 'Unauthorized.'], 401);
        }

        $userId = trim((string) ($args['id'] ?? ''));
        if ($userId === '') {
            return $this->respondWithJson($response, ['message' => 'User id is required.'], 400);
        }

        // Ownership enforcement: ordinary users/customers can only read their own record.
        // Admin/super_admin bypass.
        if (!$this->isPrivileged($identity) && $identity->getUserId() !== $userId) {
            return $this->respondWithJson($response, ['message' => 'Forbidden.'], 403);
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

    private function isPrivileged(Identity $identity): bool
    {
        $roles = array_map('strtolower', $identity->getRoles());

        return in_array('admin', $roles, true) || in_array('super_admin', $roles, true);
    }
}
