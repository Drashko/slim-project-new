<?php

declare(strict_types=1);

namespace App\Web\Shared;

use Psr\Http\Message\ServerRequestInterface;

trait LocalizedRouteTrait
{
    private function localizedPath(ServerRequestInterface $request, string $path = ''): string
    {
        $scope = $request->getAttribute('locale_scope');
        if ($scope === 'admin') {
            $normalized = trim($path);
            if ($normalized === '' || $normalized === '/') {
                return '/admin';
            }

            $trimmed = ltrim($normalized, '/');
            if (str_starts_with($trimmed, 'admin')) {
                return '/' . $trimmed;
            }
        }

        $locale = $request->getAttribute('locale');
        $locale = is_string($locale) && $locale !== '' ? $locale : 'en';

        $normalized = trim($path);
        if ($normalized === '' || $normalized === '/') {
            return '/' . $locale;
        }

        return '/' . $locale . '/' . ltrim($normalized, '/');
    }
}
