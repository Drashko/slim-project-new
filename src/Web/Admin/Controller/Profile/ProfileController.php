<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Profile;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProfileController
{
    use LocalizedRouteTrait;

    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly AdminAuthenticator $authenticator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/login'))
                ->withStatus(302);
        }

        return $this->templates->render($response, 'admin::profile/index', [
            'user' => $user,
        ]);
    }
}
