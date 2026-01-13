<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class TraceIdMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $attribute = 'trace_id',
        private string $header = 'X-Trace-Id'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $traceId = $request->getAttribute($this->attribute);
        if (!is_string($traceId) || $traceId === '') {
            $traceId = $this->generateTraceId();
            $request = $request->withAttribute($this->attribute, $traceId);
        }

        $response = $handler->handle($request);

        if (!$response->hasHeader($this->header)) {
            $response = $response->withHeader($this->header, $traceId);
        }

        return $response;
    }

    private function generateTraceId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable) {
            return uniqid('trace_', true);
        }
    }
}
