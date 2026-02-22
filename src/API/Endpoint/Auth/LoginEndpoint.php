<?php

declare(strict_types=1);

namespace App\API\Endpoint\Auth;

use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\DtoLoginInput;
use App\Feature\Login\Handler\LoginHandler;
use App\Feature\Login\ValidateLoginInput;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class LoginEndpoint
{
    use JsonResponseTrait;

    public function __construct(
        private readonly LoginHandler $handler,
        private readonly ValidateLoginInput $validator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = (array) ($request->getParsedBody() ?? []);
            $input = new DtoLoginInput((string) ($data['email'] ?? ''), (string) ($data['password'] ?? ''));
            $errors = $this->validator->validate($input);
            if ($errors !== []) {
                return $this->respondWithJson($response, ['status' => 'error', 'errors' => $errors], 422);
            }

            $result = $this->handler->handle(new LoginCommand($input->getEmail(), $input->getPassword()));

            return $this->respondWithJson($response, $result);
        } catch (Throwable $e) {
            return $this->respondWithJson($response, ['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }
}
