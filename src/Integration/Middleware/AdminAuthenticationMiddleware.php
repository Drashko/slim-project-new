<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Shared\DomainException;
use App\Integration\Authentication\AdminAuthenticator;
use App\Integration\Session\AdminSessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AdminAuthenticator $authenticator,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly AdminSessionInterface $session
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticator->authenticate($request);
        } catch (DomainException) {
            if (is_array($this->session->get('user'))) {
                $response = $this->responseFactory->createResponse(302);

                return $response->withHeader('Location', '/auth/login');
            }

            $response = $this->responseFactory->createResponse(302);

            return $response->withHeader('Location', '/admin/login');
        }

        return $handler->handle($request);
    }
}
