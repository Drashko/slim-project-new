<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Integration\Flash\FlashMessages;
use App\Integration\Session\AdminSessionInterface;
use App\Integration\Session\PublicSessionInterface;
use App\Integration\Session\SessionInterface;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LogoutController
{
    use LocalizedRouteTrait;

    public function __construct(
        private PublicSessionInterface $publicSession,
        private AdminSessionInterface $adminSession,
        private FlashMessages         $flash
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session = $this->resolveSession($request);

        foreach (['tokens', 'user'] as $key) {
            if (method_exists($session, 'delete')) {
                $session->delete($key);
            } elseif (method_exists($session, 'remove')) {
                $session->remove($key);
            } else {
                $session->set($key, null);
            }
        }

        $this->flash->addMessage('success', 'You have been signed out.');

        return $response
            ->withHeader('Location', $this->localizedPath($request, $this->redirectPath($request)))
            ->withStatus(302);
    }

    private function redirectPath(ServerRequestInterface $request): string
    {
        $scope = $request->getAttribute('locale_scope');
        if ($scope === 'admin') {
            return 'admin/login';
        }

        $redirect = $request->getQueryParams()['redirect'] ?? '';
        if (is_string($redirect)) {
            $redirect = trim($redirect);
        } else {
            $redirect = '';
        }

        return $redirect;
    }

    private function resolveSession(ServerRequestInterface $request): SessionInterface
    {
        $scope = $request->getAttribute('locale_scope');
        if ($scope === 'admin') {
            return $this->adminSession;
        }

        return $this->publicSession;
    }
}
