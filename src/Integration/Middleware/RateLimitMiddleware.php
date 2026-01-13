<?php

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class RateLimitMiddleware
{
    /**
     * @var array<string, array{count: int, expiresAt: int}>
     */
    private array $buckets = [];

    public function __construct(private readonly int $limit = 30, private readonly int $ttl = 60)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverParams = $request->getServerParams();
        $ip = $serverParams['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate:' . sha1($ip . '|' . $request->getUri()->getPath());

        $now = time();
        $bucket = $this->buckets[$key] ?? null;

        if ($bucket === null || $bucket['expiresAt'] <= $now) {
            $bucket = [
                'count' => 0,
                'expiresAt' => $now + $this->ttl,
            ];
        }

        $bucket['count']++;
        $this->buckets[$key] = $bucket;

        if ($bucket['count'] > $this->limit) {
            $response = new Response(429);

            return $response->withHeader('Retry-After', (string) $this->ttl);
        }

        return $handler->handle($request);
    }
}
