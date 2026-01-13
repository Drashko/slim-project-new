<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Integration\Middleware\SecurityHeadersMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class SecurityHeadersMiddlewareTest extends TestCase
{
    public function testAddsSecurityHeadersToResponse(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = $middleware($request, $handler);

        self::assertSame('DENY', $response->getHeaderLine('X-Frame-Options'));
        self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        self::assertSame('no-referrer', $response->getHeaderLine('Referrer-Policy'));
        self::assertSame('geolocation=(), microphone=(), camera=()', $response->getHeaderLine('Permissions-Policy'));
        self::assertSame("default-src 'none'; frame-ancestors 'none'; base-uri 'none'", $response->getHeaderLine('Content-Security-Policy'));
    }
}
