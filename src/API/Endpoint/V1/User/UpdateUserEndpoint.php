<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\User;

use App\Domain\Shared\DomainException;
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
        $userId = (string) ($args['id'] ?? '');
        if (trim($userId) === '') {
            return $this->respondWithJson($response, ['message' => 'User id is required.'], 400);
        }

        try {
            $payload = $this->readPayload($request);

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
}
