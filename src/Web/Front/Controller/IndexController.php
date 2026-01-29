<?php

declare(strict_types=1);

namespace App\Web\Front\Controller;

use App\Integration\View\TemplateRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class IndexController
{
    public function __construct(
        private TemplateRenderer $templates,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->templates->render($response, 'front::home/index', [
            'user' => null,
        ]);
    }
}
