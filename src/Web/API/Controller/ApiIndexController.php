<?php

declare(strict_types=1);

namespace App\Web\API\Controller;

use App\Integration\View\TemplateRenderer;
use App\Web\Shared\PublicUserResolver;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ApiIndexController
{
    public function __construct(
        private TemplateRenderer $templates,
        private SessionInterface $session
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = PublicUserResolver::resolve($this->session->get('user'));

        return $this->templates->render($response, 'front::api/index', [
            'user' => $user,
        ]);
    }
}
