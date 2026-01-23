<?php

declare(strict_types=1);

namespace App\Web\API\Controller;

use App\Integration\View\TemplateRenderer;
use App\Integration\Session\PublicSessionInterface;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ApiIndexController
{
    public function __construct(
        private TemplateRenderer $templates,
        private PublicSessionInterface $session
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
