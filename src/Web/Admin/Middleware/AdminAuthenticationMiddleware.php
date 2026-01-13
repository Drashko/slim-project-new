<?php

declare(strict_types=1);

namespace App\Web\Admin\Middleware;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminAuthenticationMiddleware implements MiddlewareInterface
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly AdminAuthenticator $authenticator,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticator->authenticate($request);
        } catch (DomainException) {
            $response = $this->responseFactory->createResponse(302);

            return $response->withHeader('Location', $this->localizedPath($request, 'admin/login'));
        }

        return $handler->handle($request);
    }
}
