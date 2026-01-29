<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Integration\View\TemplateRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HomeController
{
    public function __construct(
        private TemplateRenderer $templates,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->templates->render($response, 'admin::home/index', [
            'user' => null,
        ]);
    }
}
