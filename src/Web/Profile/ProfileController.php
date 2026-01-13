<?php

declare(strict_types=1);

namespace App\Web\Profile;

use App\Integration\View\TemplateRenderer;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ProfileController
{
    public function __construct(
        private TemplateRenderer $templates,
        private SessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $this->session->get('user');
        $normalizedUser = is_array($user) ? $user : null;

        return $this->templates->render($response, 'profile::overview', [
            'user' => $normalizedUser,
        ]);
    }
}
