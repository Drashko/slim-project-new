<?php

declare(strict_types=1);

namespace App\Integration\Http;

use App\Integration\View\TemplateRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class NotFoundHandler
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse(404);

        return $this->renderer->render($response, 'errors/404', [
            'requestedPath' => $request->getUri()->getPath(),
        ]);
    }
}
