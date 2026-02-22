<?php

declare(strict_types=1);

namespace App\API\Endpoint\Auth;

use App\Feature\Token\Revoke\Command\RevokeCommand;
use App\Feature\Token\Revoke\Handler\RevokeHandler;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class LogoutEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly RevokeHandler $handler)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $token = $this->resolveRefreshToken($request);
            if ($token === '') {
                // Idempotent logout
                return $this->respondWithJson($response, ['ok' => true]);
            }

            $result = $this->handler->handle(new RevokeCommand($token));
            return $this->respondWithJson($response, $result);
        } catch (Throwable $e) {
            return $this->respondWithJson($response, ['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    private function resolveRefreshToken(ServerRequestInterface $request): string
    {
        $cookie = (string) ($request->getCookieParams()['refresh_token'] ?? '');
        if (trim($cookie) !== '') {
            return trim($cookie);
        }

        $data = (array) ($request->getParsedBody() ?? []);
        $bodyToken = (string) ($data['refresh_token'] ?? '');
        if (trim($bodyToken) !== '') {
            return trim($bodyToken);
        }

        return trim($request->getHeaderLine('X-Refresh-Token'));
    }
}
