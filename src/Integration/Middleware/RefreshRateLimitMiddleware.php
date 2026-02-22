<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class RefreshRateLimitMiddleware
{
    private int $limit;
    private int $ttl;
    private static array $storage = [];

    public function __construct(int $limit = 60, int $ttl = 60)
    {
        $this->limit = $limit;
        $this->ttl = $ttl;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = 'refresh_' . $ip;
        $now = time();

        if (!isset(self::$storage[$key])) {
            self::$storage[$key] = [
                'count' => 0,
                'expires' => $now + $this->ttl
            ];
        }

        if ($now > self::$storage[$key]['expires']) {
            self::$storage[$key] = [
                'count' => 0,
                'expires' => $now + $this->ttl
            ];
        }

        self::$storage[$key]['count']++;

        if (self::$storage[$key]['count'] > $this->limit) {
            $response = new SlimResponse(429);
            $response->getBody()->write(json_encode([
                'error' => 'Too many refresh attempts'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
