<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Session\AdminSessionInterface;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminAuthenticationMiddleware implements MiddlewareInterface
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly AdminAuthenticator $authenticator,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly AdminSessionInterface $session,
        private readonly FlashMessages $flash,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticator->authenticate($request);
        } catch (DomainException) {
            if (is_array($this->session->get('user'))) {
                $this->flash->addMessage('error', $this->translator->trans('auth.login.flash.access_denied'));
                $response = $this->responseFactory->createResponse(302);

                return $response->withHeader('Location', $this->localizedPath($request, 'auth/login'));
            }

            $response = $this->responseFactory->createResponse(302);

            return $response->withHeader('Location', $this->localizedPath($request, 'admin/login'));
        }

        return $handler->handle($request);
    }
}
