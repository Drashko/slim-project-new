<?php

declare(strict_types=1);

namespace App\Integration\View;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;

final class TemplateRenderer
{
    public function __construct(private readonly Engine $engine)
    {
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $output = $this->engine->render($template, $data);

        $response->getBody()->write($output);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
