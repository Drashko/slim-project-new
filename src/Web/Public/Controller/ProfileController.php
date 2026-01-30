<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Integration\Session\PublicSessionInterface;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ProfileController
{
    public function __construct(
        private TemplateRenderer $templates,
        private PublicSessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $normalizedUser = PublicUserResolver::resolve($this->session->get('user'));

        return $this->templates->render($response, 'profile::overview', [
            'user' => $normalizedUser,
        ]);
    }
}
