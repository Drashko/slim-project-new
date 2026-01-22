<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Auth;

use App\Domain\Shared\DomainException;
use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\Handler\LoginHandler;
use App\Integration\View\TemplateRenderer;
use App\Web\Auth\Dto\RegisterFormData;
use App\Web\Shared\LocalizedRouteTrait;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LoginController
{
    use LocalizedRouteTrait;

    private ?string $lastEmail = null;

    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly LoginHandler $loginHandler,
        private readonly SessionInterface $session,
        private readonly Messages $flash,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sessionTokens = $this->session->get('tokens');
        $tokens = is_array($sessionTokens) ? $sessionTokens : null;
        $loginSucceeded = false;

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $email = trim((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');
            $this->lastEmail = $email;

            if ($email === '' || $password === '') {
                $this->flash->addMessage('error', $this->translator->trans('auth.login.flash.missing_credentials'));
            } else {
                try {
                    $tokens = $this->loginHandler->handle(new LoginCommand(
                        $email,
                        $password,
                        $request->getServerParams()['REMOTE_ADDR'] ?? null
                    ));

                    $this->session->set('tokens', $tokens);
                    $this->session->set('user', $tokens['user']);
                    $loginSucceeded = true;
                    $this->flash->addMessage('success', $this->translator->trans('auth.login.flash.welcome', [
                        '%email%' => $tokens['user']['email'] ?? $email,
                    ]));
                } catch (DomainException $exception) {
                    $tokens = null;
                    $this->clearSessionKey('tokens');
                    $this->clearSessionKey('user');
                    $this->flash->addMessage('error', $this->translator->trans($exception->getMessage()));
                }
            }
        }

        if ($loginSucceeded) {
            $userForRedirect = is_array($tokens['user'] ?? null) ? $tokens['user'] : [];

            return $response
                ->withHeader('Location', $this->resolveRedirectPath($request, $userForRedirect))
                ->withStatus(302);
        }

        $identity = is_array($tokens['user'] ?? null) ? $tokens['user'] : null;
        $user = $this->session->get('user') ?? $identity;
        $lastEmail = $this->lastEmail ?? ($identity['email'] ?? null);

        return $this->templates->render($response, 'admin::auth/login', [
            'tokens' => $tokens,
            'user' => $user,
            'last_email' => $lastEmail,
        ]);
    }

    private function clearSessionKey(string $key): void
    {
        if (method_exists($this->session, 'delete')) {
            $this->session->delete($key);

            return;
        }

        if (method_exists($this->session, 'remove')) {
            $this->session->remove($key);

            return;
        }

        $this->session->set($key, null);
    }

    private function resolveRedirectPath(ServerRequestInterface $request, array $user): string
    {
        $roles = $this->normalizeRoles($user['roles'] ?? []);

        if (in_array(RegisterFormData::ROLE_ADMIN, $roles, true)) {
            return $this->localizedPath($request, 'admin');
        }

        return $this->localizedPath($request);
    }

    /**
     * @return string[]
     */
    private function normalizeRoles(mixed $roles): array
    {
        if ($roles === null) {
            return [];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $normalized = [];
        foreach ($roles as $role) {
            if (is_scalar($role)) {
                $normalized[] = trim((string) $role);
            }
        }

        return array_values(array_filter($normalized, static fn(string $role): bool => $role !== ''));
    }
}
