<?php

declare(strict_types=1);

namespace App\Web\Shared;

use Psr\Http\Message\ServerRequestInterface;

trait LocalizedRouteTrait
{
    private function localizedPath(ServerRequestInterface $request, string $path = ''): string
    {
        $locale = $request->getAttribute('locale');
        $locale = is_string($locale) && $locale !== '' ? $locale : 'en';

        $normalized = trim($path);
        if ($normalized === '' || $normalized === '/') {
            return '/' . $locale;
        }

        return '/' . $locale . '/' . ltrim($normalized, '/');
    }
}
