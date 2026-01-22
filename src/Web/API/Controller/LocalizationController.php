<?php

declare(strict_types=1);

namespace App\Web\API\Controller;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LocalizationController
{
    public function __construct(private array $settings)
    {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $localization = (array) ($this->settings['localization'] ?? []);
        $paths = (array) ($localization['paths'] ?? []);
        $defaultLocale = (string) ($localization['default_locale'] ?? 'en');
        $locale = (string) ($args['locale'] ?? $defaultLocale);

        if (!array_key_exists($locale, $paths)) {
            $locale = $defaultLocale;
        }

        $path = $paths[$locale] ?? '';
        $payload = [];

        if (is_string($path) && $path !== '' && is_file($path)) {
            $contents = file_get_contents($path);
            if ($contents !== false) {
                $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
        }

        $response->getBody()->write((string) json_encode($payload, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
