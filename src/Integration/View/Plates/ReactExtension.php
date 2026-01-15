<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

final class ReactExtension implements ExtensionInterface
{
    /** @var array<string,array{entry:string,manifest_path:string,public_prefix:string,dev_server:string}> */
    private array $bundles;
    private string $defaultBundle;

    /**
     * @param array<string,array{entry:string,manifest_path:string,public_prefix:string,dev_server:string}>|string $bundles
     */
    public function __construct(
        array|string $bundles,
        ?string $manifestPath = null,
        ?string $publicPrefix = null,
        ?string $devServer = null,
        string $defaultBundle = 'public'
    ) {
        if (is_array($bundles)) {
            if ($manifestPath !== null && $publicPrefix === null && $devServer === null) {
                $defaultBundle = $manifestPath;
            }

            $this->bundles = $bundles;
            $this->defaultBundle = $defaultBundle;
            return;
        }

        $this->bundles = [
            'public' => [
                'entry' => $bundles,
                'manifest_path' => $manifestPath ?? '',
                'public_prefix' => $publicPrefix ?? '/assets/react/',
                'dev_server' => $devServer ?? '',
            ],
        ];
        $this->defaultBundle = 'public';
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
            $bundle = isset($options['bundle']) && is_string($options['bundle']) && $options['bundle'] !== ''
                ? $options['bundle']
                : $this->defaultBundle;

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

            static $assetsInjected = [];
            $assetMarkup = '';
            if (empty($assetsInjected[$bundle])) {
                $assetsInjected[$bundle] = true;
                $assets = $this->resolveAssets($bundle);

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
            $devPath = (string) (parse_url($devBase, PHP_URL_PATH) ?? '');
            $devPrefix = ($devPath === '' || $devPath === '/')
                ? $normalizePrefix($config['public_prefix'])
                : '/';
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
