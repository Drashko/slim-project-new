<?php

declare(strict_types=1);

namespace App\Integration\Routing;

/**
 * Maps canonical route paths to locale-specific slugs.
 *
 * The configuration expects keys to be canonical paths (without a leading slash)
 * and values to be arrays of locale => localized path, e.g.
 *
 *  [
 *      'profile' => ['en' => 'profile', 'bg' => 'profil'],
 *      'admin/users/{id}' => ['en' => 'admin/users/{id}', 'bg' => 'admin/potrebiteli/{id}'],
 *  ]
 */
final class PathLocalizer
{
    /**
     * @param array<string, array<string, string>> $routeTranslations
     */
    public function __construct(private readonly array $routeTranslations = [])
    {
    }

    public function prefix(string $path, string $locale): string
    {
        $translated = $this->translate($path, $locale);

        if ($translated === '') {
            return '/' . $locale;
        }

        return '/' . $locale . '/' . ltrim($translated, '/');
    }

    private function translate(string $path, string $locale): string
    {
        $normalizedPath = ltrim(trim($path), '/');
        if ($normalizedPath === '') {
            return '';
        }

        foreach ($this->routeTranslations as $canonical => $localized) {
            $canonicalPattern = trim((string) $canonical, '/');
            if ($canonicalPattern === '') {
                continue;
            }

            $regex = '#^' . preg_replace('#\\\{[^/]+\\\}#', '([^/]+)', preg_quote($canonicalPattern, '#')) . '$#';
            if ($regex === null || preg_match($regex, $normalizedPath, $matches) !== 1) {
                continue;
            }

            $template = $localized[$locale] ?? $canonicalPattern;
            if (!is_string($template) || $template === '') {
                $template = $canonicalPattern;
            }

            $index = 1;

            return preg_replace_callback(
                '/\{[^}]+\}/',
                static function () use (&$index, $matches): string {
                    return $matches[$index++] ?? '';
                },
                trim($template, '/')
            ) ?? trim($template, '/');
        }

        return $normalizedPath;
    }

    public function canonicalize(string $path): string
    {
        $normalizedPath = ltrim(trim($path), '/');
        if ($normalizedPath === '') {
            return '';
        }

        foreach ($this->routeTranslations as $canonical => $localized) {
            $canonicalPattern = trim((string) $canonical, '/');
            if ($canonicalPattern === '') {
                continue;
            }

            $templates = array_values(array_filter(
                array_map(
                    static fn(mixed $template): string => is_string($template) && $template !== ''
                        ? trim($template, '/')
                        : $canonicalPattern,
                    $localized
                ),
                static fn(string $template): bool => $template !== ''
            ));

            // Always include the canonical pattern itself as a match candidate.
            $templates[] = $canonicalPattern;

            foreach ($templates as $template) {
                $regex = '#^' . preg_replace('#\\\{[^/]+\\\}#', '([^/]+)', preg_quote($template, '#')) . '$#';
                if ($regex === null || preg_match($regex, $normalizedPath, $matches) !== 1) {
                    continue;
                }

                $index = 1;

                return preg_replace_callback(
                    '/\{[^}]+\}/',
                    static function () use (&$index, $matches): string {
                        return $matches[$index++] ?? '';
                    },
                    $canonicalPattern
                ) ?? $canonicalPattern;
            }
        }

        return $normalizedPath;
    }
}
