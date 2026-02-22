<?php

declare(strict_types=1);

namespace App\API\Endpoint\Auth;

use App\Feature\Token\RefreshToken\Command\RefreshCommand;
use App\Feature\Token\RefreshToken\Handler\RefreshHandler;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class RefreshEndpoint
{
    use JsonResponseTrait;

    public function __construct(private readonly RefreshHandler $handler)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $token = $this->resolveRefreshToken($request);
            if ($token === '') {
                return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Missing refresh token.'], 400);
            }

            $result = $this->handler->handle(new RefreshCommand($token));

            return $this->respondWithJson($response, $result);
        } catch (Throwable $e) {
            return $this->respondWithJson($response, ['status' => 'error', 'message' => $e->getMessage()], 401);
        }
    }

    private function resolveRefreshToken(ServerRequestInterface $request): string
    {
        // 1) cookie
        $cookie = (string) ($request->getCookieParams()['refresh_token'] ?? '');
        if (trim($cookie) !== '') {
            return trim($cookie);
        }

        // 2) body
        $data = (array) ($request->getParsedBody() ?? []);
        $bodyToken = (string) ($data['refresh_token'] ?? '');
        if (trim($bodyToken) !== '') {
            return trim($bodyToken);
        }

        // 3) header
        $header = trim($request->getHeaderLine('X-Refresh-Token'));
        return $header;
    }
}
