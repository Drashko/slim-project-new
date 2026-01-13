<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Integration\Middleware\RateLimitMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class RateLimitMiddlewareTest extends TestCase
{
    public function testReturnsTooManyRequestsWhenLimitExceeded(): void
    {
        $middleware = new RateLimitMiddleware(2, 60);
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/auth/login', ['REMOTE_ADDR' => '127.0.0.1']);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(200);
            }
        };

        $middleware($request, $handler);
        $middleware($request, $handler);
        $response = $middleware($request, $handler);

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('60', $response->getHeaderLine('Retry-After'));
    }

    public function testDelegatesToNextHandlerWhenUnderLimit(): void
    {
        $middleware = new RateLimitMiddleware(5, 30);
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/auth/login', ['REMOTE_ADDR' => '127.0.0.1']);

        $handler = new class implements RequestHandlerInterface {
            public bool $handled = false;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handled = true;

                return new Response(200);
            }
        };

        $response = $middleware($request, $handler);

        self::assertTrue($handler->handled);
        self::assertSame(200, $response->getStatusCode());
    }
}
