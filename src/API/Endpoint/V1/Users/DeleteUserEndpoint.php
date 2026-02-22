<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Users;

use App\Domain\Shared\DomainException;
use App\Domain\Token\Identity;
use App\Feature\User\Command\DeleteUserCommand;
use App\Feature\User\Handler\DeleteUserHandler;
use App\Integration\Helper\JsonResponseTrait;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class DeleteUserEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly DeleteUserHandler $deleteUserHandler)
    {
    }

    /**
     * @param array{id?: string} $args
     * @throws JsonException
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var Identity|null $identity */
        $identity = $request->getAttribute('auth.identity');
        if ($identity === null) {
            return $this->respondWithJson($response, ['message' => 'Unauthorized.'], 401);
        }

        // Only privileged roles may delete users.
        if (!$this->isPrivileged($identity)) {
            return $this->respondWithJson($response, ['message' => 'Forbidden.'], 403);
        }

        $userId = trim((string) ($args['id'] ?? ''));
        if ($userId === '') {
            return $this->respondWithJson($response, ['message' => 'User id is required.'], 400);
        }

        try {
            $this->deleteUserHandler->handle(new DeleteUserCommand($userId));

            return $this->respondWithJson($response, ['message' => 'User deleted successfully.']);
        } catch (DomainException $exception) {
            $status = $exception->getMessage() === 'User not found.' ? 404 : 400;

            return $this->respondWithJson($response, ['message' => $exception->getMessage()], $status);
        } catch (Throwable) {
            return $this->respondWithJson($response, ['message' => 'Unable to delete user.'], 500);
        }
    }

    private function isPrivileged(Identity $identity): bool
    {
        $roles = array_map('lowermost', $identity->getRoles());

        return in_array('admin', $roles, true) || in_array('super_admin', $roles, true);
    }
}
