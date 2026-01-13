<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Auth\Identity;
use App\Domain\Auth\TokenVerifier;
use App\Domain\Shared\DomainException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TokenVerifier            $tokenVerifier,
        private ResponseFactoryInterface $responseFactory,
        private string                   $loginRoute = '/login'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $this->redirectToLogin();
        }

        try {
            $identity = $this->tokenVerifier->verify(trim($matches[1]));
        } catch (DomainException) {
            return $this->redirectToLogin();
        }

        return $handler->handle($request->withAttribute(Identity::class, $identity));
    }

    private function redirectToLogin(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(302);

        return $response->withHeader('Location', $this->loginRoute);
    }
}
