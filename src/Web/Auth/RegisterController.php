<?php

declare(strict_types=1);

namespace App\Web\Auth;

use App\Domain\Shared\DomainException;
use App\Integration\View\TemplateRenderer;
use App\Feature\Register\Command\RegisterUserCommand;
use App\Feature\Register\Handler\RegisterUserHandler;
use App\Web\Auth\Dto\RegisterFormData;
use App\Web\Auth\Form\RegisterFormType;
use App\Web\Shared\PublicUserResolver;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RegisterController
{
    public function __construct(
        private TemplateRenderer         $templates,
        private RegisterUserHandler      $registerUserHandler,
        private Messages                $flash,
        private SessionInterface        $session,
        private TranslatorInterface     $translator,
        private FormFactoryInterface    $formFactory
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $form = $this->formFactory->create(RegisterFormType::class, new RegisterFormData());

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

                    return $response->withHeader('Location', $this->localizedPath($request, 'profile/login'))->withStatus(302);

                } catch (DomainException $exception) {
                    $message = $this->translator->trans($exception->getMessage());
                    $form->addError(new FormError($message));
                }
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
