<?php

declare(strict_types=1);

namespace App\Integration\Http;

use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class NotFoundHandler
{
    use JsonResponseTrait;

    public function __construct(
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

        return $this->respondWithJson($response, [
            'error' => 'Not Found',
            'path' => $request->getUri()->getPath(),
        ], 404);
    }
}
