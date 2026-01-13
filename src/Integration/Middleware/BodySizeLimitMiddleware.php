<?php

namespace App\Integration\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class BodySizeLimitMiddleware
{
    public function __construct(private int $maxBytes = 1_000_000)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $length = (int) ($request->getHeaderLine('Content-Length') ?: 0);

        if ($length > $this->maxBytes) {
            return new Response(413);
        }

        return $handler->handle($request);
    }
}
