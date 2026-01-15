<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

final class ViteExtension implements ExtensionInterface
{
    /** @var array<string,array{entry:string,manifest_path:string,public_prefix:string,dev_server:string}> */
    private array $bundles;

    /** @param array<string,array{entry:string,manifest_path:string,public_prefix:string,dev_server:string}> $bundles */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    public function register(Engine $engine): void
    {
        $engine->registerFunction('vite_assets', function (string $bundle): string {
            $assets = $this->resolveAssets($bundle);

            if (!$assets['available']) {
                return '';
            }

            $markup = '';
            foreach ($assets['styles'] as $stylesheet) {
                $href = htmlspecialchars($stylesheet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $markup .= sprintf('<link rel="stylesheet" href="%s">', $href) . PHP_EOL;
            }

            foreach ($assets['scripts'] as $script) {
                $src = htmlspecialchars($script, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $markup .= sprintf('<script type="module" src="%s"></script>', $src) . PHP_EOL;
            }

            return $markup;
        });
    }

    /** @return array{mode:string,available:bool,scripts:array<int,string>,styles:array<int,string>} */
    private function resolveAssets(string $bundle): array
    {
        if (!isset($this->bundles[$bundle])) {
            return [
                'mode' => 'missing',
                'available' => false,
                'scripts' => [],
                'styles' => [],
            ];
        }

        $config = $this->bundles[$bundle];

        $normalizePrefix = static function (string $prefix): string {
            $clean = str_replace('\\', '/', trim($prefix));
            if ($clean === '') {
                return '/';
            }

            return rtrim($clean, '/') . '/';
        };

        $buildAssetUrl = function (string $path) use ($normalizePrefix, $config): string {
            $normalizedPrefix = $normalizePrefix($config['public_prefix']);
            $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

            return $normalizedPrefix . $normalizedPath;
        };

        $devServer = trim($config['dev_server']);
        if ($devServer !== '') {
            $devBase = rtrim($devServer, '/');
            $devPrefix = $normalizePrefix($config['public_prefix']);
            $entry = ltrim($config['entry'], '/');

            return [
                'mode' => 'dev',
                'available' => true,
                'scripts' => [
                    $devBase . $devPrefix . '@vite/client',
                    $devBase . $devPrefix . $entry,
                ],
                'styles' => [],
            ];
        }

        $manifestPath = $config['manifest_path'];
        if ($manifestPath === '' || !is_file($manifestPath) || !is_readable($manifestPath)) {
            return [
                'mode' => 'manifest-missing',
                'available' => false,
                'scripts' => [],
                'styles' => [],
            ];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $entry = is_array($manifest) && isset($manifest[$config['entry']]) && is_array($manifest[$config['entry']])
            ? $manifest[$config['entry']]
            : null;

        if ($entry === null) {
            return [
                'mode' => 'manifest-missing',
                'available' => false,
                'scripts' => [],
                'styles' => [],
            ];
        }

        $scripts = [];
        $styles = [];

        if (!empty($entry['file'])) {
            $scripts[] = $buildAssetUrl((string) $entry['file']);
        }

        foreach ((array) ($entry['css'] ?? []) as $stylesheet) {
            if (is_string($stylesheet) && $stylesheet !== '') {
                $styles[] = $buildAssetUrl($stylesheet);
            }
        }

        return [
            'mode' => 'manifest',
            'available' => $scripts !== [] || $styles !== [],
            'scripts' => $scripts,
            'styles' => $styles,
        ];
    }
}
