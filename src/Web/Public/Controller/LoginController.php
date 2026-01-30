<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\Handler\LoginHandler;
use App\Integration\Flash\FlashMessages;
use App\Integration\Session\PublicSessionInterface;
use App\Integration\View\TemplateRenderer;
use App\Web\Public\DTO\LoginFormData;
use App\Web\Public\DTO\RegisterFormData;
use App\Web\Public\Form\PublicLoginFormType;
use App\Web\Shared\LocalizedRouteTrait;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LoginController
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly LoginHandler $loginHandler,
        private readonly PublicSessionInterface $session,
        private readonly FlashMessages $flash,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $loginSucceeded = false;

        $sessionUser = PublicUserResolver::resolve($this->session->get('user'));
        $defaultEmail = $sessionUser['email'] ?? null;

        $formData = new LoginFormData();
        $formData->email = $defaultEmail;

        $form = $this->formFactory->create(PublicLoginFormType::class, $formData);

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $submittedData = is_array($parsedBody) ? $parsedBody : [];
            $formName = $form->getName();
            if (isset($submittedData[$formName]) && is_array($submittedData[$formName])) {
                $submittedData = $submittedData[$formName];
            }
            $form->submit($submittedData);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var LoginFormData $data */
                $data = $form->getData();

                try {
                    $loginResult = $this->loginHandler->handle(new LoginCommand(
                        $data->getEmail(),
                        $data->getPassword(),
                        $request->getServerParams()['REMOTE_ADDR'] ?? null
                    ));

                    $user = $loginResult['user'] ?? [];
                    $this->session->set('user', $user);
                    if ($this->isAllowedRole($user)) {
                        $this->flash->addMessage('success', $this->translator->trans('auth.login.flash.welcome', [
                            '%email%' => $user['email'] ?? $data->getEmail(),
                        ]));

                        $loginSucceeded = true;
                    } else {
                        $this->clearSessionKey('user');
                        $message = $this->translator->trans('auth.login.flash.user_not_found');
                        $this->flash->addMessage('error', $message);
                        $form->addError(new FormError($message));
                    }
                } catch (DomainException $exception) {
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

        $user = $sessionUser;

        return $this->templates->render($response, 'auth::login', [
            'user' => $user,
            'form' => $form->createView(),
            'flash' => $this->flash,
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
                $normalized[] = strtoupper(trim((string) $role));
            }
        }

        return array_values(array_filter($normalized, static fn(string $role): bool => $role !== ''));
    }
}
