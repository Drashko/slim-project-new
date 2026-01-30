<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\Handler\LoginHandler;
use App\Integration\Flash\FlashMessages;
use App\Integration\Rbac\Policy;
use App\Integration\Session\AdminSessionInterface;
use App\Integration\View\TemplateRenderer;
use App\Web\Admin\DTO\AdminLoginFormData;
use App\Web\Admin\Form\AdminLoginFormType;
use App\Web\Public\DTO\RegisterFormData;
use App\Web\Shared\LocalizedRouteTrait;
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
        private readonly AdminSessionInterface $session,
        private readonly FlashMessages $flash,
        private readonly Policy $policy,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $loginSucceeded = false;
        $sessionUser = $this->session->get('user');

        $defaultEmail = $sessionUser['email'] ?? null;

        $formData = new AdminLoginFormData();
        $formData->email = $defaultEmail;
        $form = $this->formFactory->createBuilder(AdminLoginFormType::class, $formData)
            ->setMethod('POST')
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            $submittedData = is_array($parsedBody) ? $parsedBody : [];
            $form->submit($submittedData);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var AdminLoginFormData $data */
                $data = $form->getData();

                try {
                    $loginResult = $this->loginHandler->handle(new LoginCommand(
                        $data->getEmail(),
                        $data->getPassword(),
                        $request->getServerParams()['REMOTE_ADDR'] ?? null
                    ));

                    $user = $loginResult['user'] ?? [];
                    if ($this->isAllowedRole($user)) {
                        $this->session->set('user', $user);
                        $loginSucceeded = true;
                        $this->flash->addMessage('success', $this->translator->trans('auth.login.flash.welcome', [
                            '%email%' => $user['email'] ?? $data->getEmail(),
                        ]));
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
                ->withHeader('Location', $this->localizedPath($request, 'admin'))
                ->withStatus(302);
        }

        $user = $sessionUser;

        return $this->templates->render($response, 'admin::auth/login', [
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

        if (in_array(RegisterFormData::ROLE_ADMIN, $roles, true)) {
            return true;
        }

        return $this->policy->isGranted($roles, 'admin.access', $this->resolveRolesVersion($user));
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

    private function resolveRolesVersion(array $user): ?int
    {
        $version = $user['roles_version'] ?? $user['rolesVersion'] ?? null;
        if (is_numeric($version)) {
            return (int) $version;
        }

        return null;
    }
}
