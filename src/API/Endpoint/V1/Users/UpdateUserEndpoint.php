<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Users;

use App\Domain\Shared\DomainException;
use App\Domain\Token\Identity;
use App\Domain\User\UserInterface;
use App\Feature\Admin\User\Command\UpdateUserCommand;
use App\Feature\Admin\User\Handler\UpdateUserHandler;
use App\Integration\Helper\JsonResponseTrait;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class UpdateUserEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly UpdateUserHandler $updateUserHandler)
    {
    }

    /**
     * @param array{id?: string} $args
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var Identity|null $identity */
        $identity = $request->getAttribute('auth.identity');
        if ($identity === null) {
            return $this->respondWithJson($response, ['message' => 'Unauthorized.'], 401);
        }

        $userId = (string) ($args['id'] ?? '');
        if (trim($userId) === '') {
            return $this->respondWithJson($response, ['message' => 'User id is required.'], 400);
        }

        // Ownership enforcement:
        // - admin/super_admin may update any user
        // - ordinary users/customers may only update their own record
        if (!$this->isPrivileged($identity) && $identity->getUserId() !== $userId) {
            return $this->respondWithJson($response, ['message' => 'Forbidden.'], 403);
        }

        try {
            $payload = $this->readPayload($request);

            // Prevent privilege escalation: non-privileged users cannot change roles/status.
            if (!$this->isPrivileged($identity)) {
                unset($payload['roles'], $payload['status']);
            }

            $command = new UpdateUserCommand(
                $userId,
                isset($payload['email']) ? (string) $payload['email'] : null,
                isset($payload['password']) ? (string) $payload['password'] : null,
                isset($payload['roles']) && is_array($payload['roles']) ? $payload['roles'] : null,
                isset($payload['status']) ? (string) $payload['status'] : null,
            );

            $user = $this->updateUserHandler->handle($command);

            return $this->respondWithJson($response, [
                'message' => 'User updated successfully.',
                'user' => $this->transformUser($user),
            ]);
        } catch (DomainException|\InvalidArgumentException $exception) {
            $status = $exception->getMessage() === 'User not found.' ? 404 : 400;

            return $this->respondWithJson($response, ['message' => $exception->getMessage()], $status);
        } catch (JsonException) {
            return $this->respondWithJson($response, ['message' => 'Invalid JSON body.'], 400);
        } catch (Throwable) {
            return $this->respondWithJson($response, ['message' => 'Unable to update user.'], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(ServerRequestInterface $request): array
    {
        $body = (string) $request->getBody();
        if (trim($body) === '') {
            return [];
        }

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
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
