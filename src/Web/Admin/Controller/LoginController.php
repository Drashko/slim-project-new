<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Login\Command\LoginCommand;
use App\Feature\Login\Handler\LoginHandler;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Integration\Rbac\Policy;
use App\Integration\Session\AdminSessionInterface;
use App\Web\Admin\DTO\AdminLoginFormData;
use App\Web\Public\DTO\RegisterFormData;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LoginController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private readonly LoginHandler $loginHandler,
        private readonly AdminSessionInterface $session,
        private readonly FlashMessages $flash,
        private readonly Policy $policy,
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $loginSucceeded = false;
        $loggedInUser = null;
        $sessionUser = $this->session->get('user');

        $defaultEmail = $sessionUser['email'] ?? null;

        $formData = new AdminLoginFormData();
        $formData->email = $defaultEmail;
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $submittedData = $this->resolveSubmittedData($request, ['admin_login_form']);
            $formData->email = isset($submittedData['email']) ? (string) $submittedData['email'] : $defaultEmail;
            $formData->password = isset($submittedData['password']) ? (string) $submittedData['password'] : null;
            $errors = $this->collectValidationErrors($formData);

            if ($errors === []) {
                try {
                    $loginResult = $this->loginHandler->handle(new LoginCommand(
                        $formData->getEmail(),
                        $formData->getPassword(),
                        $request->getServerParams()['REMOTE_ADDR'] ?? null
                    ));

                    $user = $loginResult['user'] ?? [];
                    if ($this->isAllowedRole($user)) {
                        $this->session->set('user', $user);
                        $loginSucceeded = true;
                        $loggedInUser = $user;
                        $this->flash->addMessage('success', $this->translator->trans('auth.login.flash.welcome', [
                            '%email%' => $user['email'] ?? $formData->getEmail(),
                        ]));
                    } else {
                        $this->clearSessionKey('user');
                        $message = $this->translator->trans('auth.login.flash.user_not_found');
                        $this->flash->addMessage('error', $message);
                        $errors[] = $message;
                    }
                } catch (DomainException $exception) {
                    $this->clearSessionKey('user');
                    $message = $this->translator->trans($exception->getMessage());
                    $this->flash->addMessage('error', $message);
                    $errors[] = $message;
                }
            } else {
                $this->flash->addMessage('error', $this->translator->trans('auth.login.flash.missing_credentials'));
            }
        }

        if ($loginSucceeded) {
            return $this->respondWithJson($response, [
                'status' => 'ok',
                'user' => $loggedInUser,
                'redirect' => $this->localizedPath($request, 'admin'),
                'messages' => $this->flash->getMessages(),
            ]);
        }

        $payload = [
            'status' => $request->getMethod() === 'POST' ? 'error' : 'idle',
            'user' => $sessionUser,
            'errors' => $errors,
            'messages' => $this->flash->getMessages(),
        ];

        $statusCode = $request->getMethod() === 'POST' ? 400 : 200;

        return $this->respondWithJson($response, $payload, $statusCode);
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

    /**
     * @return array<string>
     */
    private function collectValidationErrors(AdminLoginFormData $data): array
    {
        $errors = [];
        foreach ($this->validator->validate($data) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    /**
     * @param list<string> $nestedKeys
     *
     * @return array<string, mixed>
     */
    private function resolveSubmittedData(ServerRequestInterface $request, array $nestedKeys): array
    {
        $parsedBody = $request->getParsedBody();
        $submittedData = is_array($parsedBody) ? $parsedBody : [];

        foreach ($nestedKeys as $nestedKey) {
            if (isset($submittedData[$nestedKey]) && is_array($submittedData[$nestedKey])) {
                return $submittedData[$nestedKey];
            }
        }

        return $submittedData;
    }
}
