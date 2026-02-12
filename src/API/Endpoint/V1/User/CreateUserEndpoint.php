<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\User;

use App\Domain\Shared\DomainException;
use App\Domain\User\UserInterface;
use App\Feature\Admin\User\Command\CreateUserCommand;
use App\Feature\Admin\User\Handler\CreateUserHandler;
use App\Integration\Helper\JsonResponseTrait;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class CreateUserEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly CreateUserHandler $createUserHandler)
    {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        unset($args);

        try {
            $payload = $this->readPayload($request);

            $command = new CreateUserCommand(
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? ''),
                is_array($payload['roles'] ?? null) ? $payload['roles'] : ['ROLE_USER'],
                isset($payload['status']) ? (string) $payload['status'] : 'Active',
            );

            $user = $this->createUserHandler->handle($command);

            return $this->respondWithJson($response, [
                'message' => 'User created successfully.',
                'user' => $this->transformUser($user),
            ], 201);
        } catch (DomainException|\InvalidArgumentException $exception) {
            return $this->respondWithJson($response, ['message' => $exception->getMessage()], 400);
        } catch (JsonException) {
            return $this->respondWithJson($response, ['message' => 'Invalid JSON body.'], 400);
        } catch (Throwable) {
            return $this->respondWithJson($response, ['message' => 'Unable to create user.'], 500);
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
