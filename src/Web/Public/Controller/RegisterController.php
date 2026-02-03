<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Register\Command\RegisterUserCommand;
use App\Feature\Register\Handler\RegisterUserHandler;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Integration\Session\PublicSessionInterface;
use App\Web\Public\DTO\RegisterFormData;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RegisterController
{
    use JsonResponseTrait;

    public function __construct(
        private RegisterUserHandler      $registerUserHandler,
        private FlashMessages            $flash,
        private PublicSessionInterface   $session,
        private TranslatorInterface     $translator,
        private ValidatorInterface      $validator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $errors = [];
        $formData = new RegisterFormData();

        if ($request->getMethod() === 'POST') {
            $submittedData = $this->resolveSubmittedData($request, ['public_register_form']);
            $formData->email = isset($submittedData['email']) ? (string) $submittedData['email'] : null;
            $formData->password = isset($submittedData['password']) ? (string) $submittedData['password'] : null;
            $formData->confirmPassword = isset($submittedData['confirmPassword']) ? (string) $submittedData['confirmPassword'] : null;
            $formData->accountType = isset($submittedData['accountType']) ? (string) $submittedData['accountType'] : $formData->accountType;

            $errors = $this->collectValidationErrors($formData);

            if ($errors === []) {
                try {
                    $this->registerUserHandler->handle(new RegisterUserCommand(
                        $formData->getEmail(),
                        $formData->getPassword(),
                        $formData->getRoles()
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('auth.register.flash.success'));

                    return $this->respondWithJson($response, [
                        'status' => 'ok',
                        'redirect' => $this->localizedPath($request, 'auth/login'),
                        'messages' => $this->flash->getMessages(),
                    ]);

                } catch (DomainException $exception) {
                    $message = $this->translator->trans($exception->getMessage());
                    $this->flash->addMessage('error', $message);
                    $errors[] = $message;
                }
            } else {
                $this->flash->addMessage('error', $this->translator->trans('auth.register.flash.invalid'));
            }
        }

        $payload = [
            'status' => $request->getMethod() === 'POST' ? 'error' : 'idle',
            'user' => PublicUserResolver::resolve($this->session->get('user')),
            'errors' => $errors,
            'messages' => $this->flash->getMessages(),
        ];

        $statusCode = $request->getMethod() === 'POST' ? 400 : 200;

        return $this->respondWithJson($response, $payload, $statusCode);
    }

    private function localizedPath(ServerRequestInterface $request, string $path = ''): string
    {
        $locale = $request->getAttribute('locale');
        $locale = is_string($locale) && $locale !== '' ? $locale : $this->translator->getLocale();

        if (!is_string($locale) || $locale === '') {
            $locale = 'en';
        }

        $normalized = trim($path);
        if ($normalized === '' || $normalized === '/') {
            return '/' . $locale;
        }

        return '/' . $locale . '/' . ltrim($normalized, '/');
    }

    /**
     * @return array<string>
     */
    private function collectValidationErrors(RegisterFormData $data): array
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
