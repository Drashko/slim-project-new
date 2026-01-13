<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Integration\Middleware\BodySizeLimitMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class BodySizeLimitMiddlewareTest extends TestCase
{
    public function testReturnsPayloadTooLargeResponseWhenLimitExceeded(): void
    {
        $middleware = new BodySizeLimitMiddleware(100);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/upload')
            ->withHeader('Content-Length', '150');

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = $middleware($request, $handler);

        self::assertSame(413, $response->getStatusCode());
    }

    public function testDelegatesToNextHandlerWhenWithinLimit(): void
    {
        $middleware = new BodySizeLimitMiddleware(200);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/upload')
            ->withHeader('Content-Length', '150');

        $handler = new class implements RequestHandlerInterface {
            public bool $handled = false;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handled = true;

                return new Response(204);
            }
        };

        $response = $middleware($request, $handler);

        self::assertTrue($handler->handled);
        self::assertSame(204, $response->getStatusCode());
    }
}
