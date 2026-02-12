<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\User;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\User\Command\DeleteUserCommand;
use App\Feature\Admin\User\Handler\DeleteUserHandler;
use App\Integration\Helper\JsonResponseTrait;
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
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        unset($request);

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
}
