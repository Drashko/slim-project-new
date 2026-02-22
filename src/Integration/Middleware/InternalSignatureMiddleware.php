<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Internal HMAC signature middleware.
 *
 * Intended for "internal" routes that must only be called by the Next.js BFF.
 *
 * Expected headers:
 * - X-Internal-Timestamp: unix seconds
 * - X-Internal-Body-Hash: sha256 hex of raw request body
 * - X-Internal-Signature: hex HMAC-SHA256 of canonical string
 *
 * Canonical string (must match Next):
 *   METHOD \n PATH?QUERY \n TS \n BODY_HASH
 */
final readonly class InternalSignatureMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $secret,
        private ResponseFactoryInterface $responseFactory,
        private int $maxSkewSeconds = 60
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ts = trim($request->getHeaderLine('X-Internal-Timestamp'));
        $bodyHash = trim($request->getHeaderLine('X-Internal-Body-Hash'));
        $sig = trim($request->getHeaderLine('X-Internal-Signature'));

        if ($this->secret === '' || $ts === '' || $bodyHash === '' || $sig === '') {
            return $this->json(403, 'Missing internal signature');
        }

        $now = time();
        $tsInt = (int) $ts;
        if ($tsInt <= 0 || abs($now - $tsInt) > $this->maxSkewSeconds) {
            return $this->json(403, 'Stale internal signature');
        }

        $rawBody = (string) $request->getBody();
        if ($request->getBody()->isSeekable()) {
            $request->getBody()->rewind();
        }

        $computedBodyHash = hash('sha256', $rawBody);
        if (!hash_equals($computedBodyHash, $bodyHash)) {
            return $this->json(403, 'Body hash mismatch');
        }

        $method = strtoupper($request->getMethod());
        $uri = $request->getUri();
        $pathWithQuery = $uri->getPath() . ($uri->getQuery() !== '' ? ('?' . $uri->getQuery()) : '');

        $canonical = $method . "\n" . $pathWithQuery . "\n" . $ts . "\n" . $bodyHash;
        $computedSig = hash_hmac('sha256', $canonical, $this->secret);

        if (!hash_equals($computedSig, $sig)) {
            return $this->json(403, 'Bad internal signature');
        }

        return $handler->handle($request);
    }

    private function json(int $status, string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write((string) (json_encode([
            'status' => 'error',
            'message' => $message,
        ], JSON_UNESCAPED_SLASHES) ?: ''));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
