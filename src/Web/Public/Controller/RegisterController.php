<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Register\Command\RegisterUserCommand;
use App\Feature\Register\Handler\RegisterUserHandler;
use App\Integration\Flash\FlashMessages;
use App\Integration\Session\PublicSessionInterface;
use App\Integration\View\TemplateRenderer;
use App\Web\Public\DTO\RegisterFormData;
use App\Web\Public\Form\PublicRegisterFormType;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RegisterController
{
    public function __construct(
        private TemplateRenderer         $templates,
        private RegisterUserHandler      $registerUserHandler,
        private FlashMessages            $flash,
        private PublicSessionInterface   $session,
        private TranslatorInterface     $translator,
        private FormFactoryInterface    $formFactory
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $form = $this->formFactory->create(PublicRegisterFormType::class, new RegisterFormData());

        if ($request->getMethod() === 'POST') {
            $form->submit((array) ($request->getParsedBody() ?? []));

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var RegisterFormData $data */
                $data = $form->getData();

                try {
                    $this->registerUserHandler->handle(new RegisterUserCommand(
                        $data->getEmail(),
                        $data->getPassword(),
                        $data->getRoles()
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('auth.register.flash.success'));

                    return $response->withHeader('Location', $this->localizedPath($request, 'auth/login'))->withStatus(302);

                } catch (DomainException $exception) {
                    $message = $this->translator->trans($exception->getMessage());
                    $this->flash->addMessage('error', $message);
                    $form->addError(new FormError($message));
                }
            } elseif ($form->isSubmitted()) {
                $this->flash->addMessage('error', $this->translator->trans('auth.register.flash.invalid'));
            }
        }

        return $this->templates->render($response, 'auth::register', [
            'form' => $form->createView(),
            'user' => PublicUserResolver::resolve($this->session->get('user')),
        ]);
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
}
