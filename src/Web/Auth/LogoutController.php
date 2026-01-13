<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Web\Shared\LocalizedRouteTrait;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

final readonly class LogoutController
{
    use LocalizedRouteTrait;

    public function __construct(
        private SessionInterface $session,
        private Messages         $flash
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        foreach (['tokens', 'user'] as $key) {
            if (method_exists($this->session, 'delete')) {
                $this->session->delete($key);
            } elseif (method_exists($this->session, 'remove')) {
                $this->session->remove($key);
            } else {
                $this->session->set($key, null);
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
}
