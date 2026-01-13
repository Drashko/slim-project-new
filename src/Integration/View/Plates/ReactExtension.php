<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

final class ReactExtension implements ExtensionInterface
{
    public function __construct(
        private readonly string $entry = 'src/main.jsx',
        private readonly string $manifestPath = '',
        private readonly string $publicPrefix = '/assets/react/',
        private readonly string $devServer = ''
    ) {
    }

    public function register(Engine $engine): void
    {
        $engine->registerFunction('react_mount', function (
            string $id,
            array $props = [],
            array $options = []
        ): string {
            $component = isset($options['component']) && is_string($options['component']) && $options['component'] !== ''
                ? $options['component']
                : 'App';
            $className = isset($options['class']) && is_string($options['class']) ? trim($options['class']) : '';
            $attributes = isset($options['attributes']) && is_array($options['attributes']) ? $options['attributes'] : [];

            $propsJson = json_encode($props, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
            if ($propsJson === false) {
                $propsJson = '{}';
            }

            $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeComponent = htmlspecialchars($component, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeProps = htmlspecialchars($propsJson, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $htmlAttributes = '';
            foreach ($attributes as $key => $value) {
                if (!is_string($key) || $key === '') {
                    continue;
                }

                if (is_bool($value)) {
                    if ($value) {
                        $htmlAttributes .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }
                    continue;
                }

                if (is_scalar($value)) {
                    $htmlAttributes .= sprintf(
                        ' %s="%s"',
                        htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                        htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    );
                }
            }

            if ($className !== '') {
                $htmlAttributes .= sprintf(' class="%s"', htmlspecialchars($className, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            }

            $dataAttributes = sprintf(' data-react-component="%s"', $safeComponent);
            if ($props !== []) {
                $dataAttributes .= sprintf(' data-react-props="%s"', $safeProps);
            }

            static $assetsInjected = false;
            $assetMarkup = '';
            if (!$assetsInjected) {
                $assetsInjected = true;
                $assets = $this->resolveAssets();

                if (!empty($assets['available'])) {
                    foreach ((array) ($assets['styles'] ?? []) as $stylesheet) {
                        $href = htmlspecialchars((string) $stylesheet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $assetMarkup .= sprintf('<link rel="stylesheet" href="%s">', $href) . PHP_EOL;
                    }

                    foreach ((array) ($assets['scripts'] ?? []) as $script) {
                        $src = htmlspecialchars((string) $script, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $assetMarkup .= sprintf('<script type="module" src="%s"></script>', $src) . PHP_EOL;
                    }
                }
            }

            return $assetMarkup . sprintf('<div id="%s"%s%s></div>', $safeId, $dataAttributes, $htmlAttributes);
        });
    }

    /**
     * @return array{mode:string,available:bool,scripts:array<int,string>,styles:array<int,string>}
     */
    private function resolveAssets(): array
    {
        $normalizePrefix = static function (string $prefix): string {
            $clean = str_replace('\\', '/', trim($prefix));
            if ($clean === '') {
                return '/';
            }

            return rtrim($clean, '/') . '/';
        };

        $buildAssetUrl = function (string $path) use ($normalizePrefix): string {
            $normalizedPrefix = $normalizePrefix($this->publicPrefix);
            $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

            return $normalizedPrefix . $normalizedPath;
        };

        if ($this->devServer !== '') {
            $devBase = rtrim($this->devServer, '/');

            return [
                'mode' => 'dev',
                'available' => true,
                'scripts' => [
                    $devBase . '/@vite/client',
                    $devBase . '/' . ltrim($this->entry, '/'),
                ],
                'styles' => [],
            ];
        }

        if ($this->manifestPath === '' || !is_file($this->manifestPath) || !is_readable($this->manifestPath)) {
            return [
                'mode' => 'manifest-missing',
                'available' => false,
                'scripts' => [],
                'styles' => [],
            ];
        }

        $manifest = json_decode((string) file_get_contents($this->manifestPath), true);
        $entry = is_array($manifest) && isset($manifest[$this->entry]) && is_array($manifest[$this->entry])
            ? $manifest[$this->entry]
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
