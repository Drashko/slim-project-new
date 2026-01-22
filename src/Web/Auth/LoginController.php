<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Domain\Shared\DomainException;
use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\Handler\LoginHandler;
use App\Integration\View\TemplateRenderer;
use App\Web\Auth\Dto\LoginFormData;
use App\Web\Auth\Form\LoginFormType;
use App\Web\Auth\Dto\RegisterFormData;
use App\Web\Shared\LocalizedRouteTrait;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LoginController
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly LoginHandler $loginHandler,
        private readonly SessionInterface $session,
        private readonly Messages $flash,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sessionTokens = $this->session->get('tokens');
        $tokens = is_array($sessionTokens) ? $sessionTokens : null;
        $loginSucceeded = false;

        $identity = is_array($tokens['user'] ?? null) ? $tokens['user'] : null;
        $defaultEmail = $identity['email'] ?? (($this->session->get('user') ?? [])['email'] ?? null);

        $formData = new LoginFormData();
        $formData->email = $defaultEmail;

        $form = $this->formFactory->create(LoginFormType::class, $formData);

        if ($request->getMethod() === 'POST') {
            $form->submit((array) ($request->getParsedBody() ?? []));

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var LoginFormData $data */
                $data = $form->getData();

                try {
                    $tokens = $this->loginHandler->handle(new LoginCommand(
                        $data->getEmail(),
                        $data->getPassword(),
                        $request->getServerParams()['REMOTE_ADDR'] ?? null
                    ));

                    $this->session->set('tokens', $tokens);
                    $this->session->set('user', $tokens['user']);
                    if ($this->isAllowedRole($tokens['user'] ?? [])) {
                        $this->flash->addMessage('success', $this->translator->trans('auth.login.flash.welcome', [
                            '%email%' => $tokens['user']['email'] ?? $data->getEmail(),
                        ]));

                        $identity = $tokens['user'] ?? $identity;
                        $loginSucceeded = true;
                    } else {
                        $tokens = null;
                        $this->clearSessionKey('tokens');
                        $this->clearSessionKey('user');
                        $message = $this->translator->trans('auth.login.flash.user_not_found');
                        $this->flash->addMessage('error', $message);
                        $form->addError(new FormError($message));
                    }
                } catch (DomainException $exception) {
                    $tokens = null;
                    $this->clearSessionKey('tokens');
                    $this->clearSessionKey('user');
                    $message = $this->translator->trans($exception->getMessage());
                    $this->flash->addMessage('error', $message);
                    $form->addError(new FormError($message));
                }
            } elseif ($form->isSubmitted()) {
                $this->flash->addMessage('error', $this->translator->trans('auth.login.flash.missing_credentials'));
            }
        }

        if ($loginSucceeded) {
            return $response
                ->withHeader('Location', $this->localizedPath($request))
                ->withStatus(302);
        }

        $user = $this->session->get('user') ?? $identity;

        return $this->templates->render($response, 'auth::login', [
            'tokens' => $tokens,
            'user' => $user,
            'form' => $form->createView(),
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

    private function isAllowedRole(array $user): bool
    {
        $roles = $this->normalizeRoles($user['roles'] ?? []);

        return in_array(RegisterFormData::ROLE_USER, $roles, true);
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
