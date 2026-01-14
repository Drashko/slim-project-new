<?php

declare(strict_types=1);

namespace App\Integration\View;

use League\Plates\Engine;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

final class TemplateRenderer
{
    public function __construct(
        private readonly Engine $engine,
        private readonly ?CacheItemPoolInterface $cache = null,
        private readonly array $cacheSettings = []
    )
    {
    }

    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        $output = $this->renderTemplate($template, $data);

        $response->getBody()->write($output);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function renderTemplate(string $template, array $data): string
    {
        if (!$this->isCacheEnabled()) {
            return $this->engine->render($template, $data);
        }

        $cacheMetadata = is_array($data['_cache'] ?? null) ? $data['_cache'] : [];
        $cacheKey = $this->resolveCacheKey($template, $data, $cacheMetadata);
        if ($cacheKey === null) {
            return $this->engine->render($template, $data);
        }

        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            if (is_string($cached)) {
                return $cached;
            }
        }

        $output = $this->engine->render($template, $data);
        $cacheItem->set($output);

        $ttl = $cacheMetadata['ttl'] ?? ($this->cacheSettings['ttl'] ?? null);
        if (is_int($ttl) && $ttl > 0) {
            $cacheItem->expiresAfter($ttl);
        }

        $this->cache->save($cacheItem);

        return $output;
    }

    private function isCacheEnabled(): bool
    {
        return $this->cache !== null && !empty($this->cacheSettings['enabled']);
    }

    private function resolveCacheKey(string $template, array $data, array $cacheMetadata = []): ?string
    {
        if (!empty($cacheMetadata['key'])) {
            $key = $cacheMetadata['key'];
            if (is_string($key) && $key !== '') {
                return $this->normalizeCacheKey($key);
            }
        }

        $cacheableData = $this->normalizeCacheData($data);
        if ($cacheableData === null) {
            return null;
        }

        $context = [
            'template' => $template,
            'data' => $cacheableData,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        ];

        $payload = json_encode($context);
        if ($payload === false) {
            return null;
        }

        return $this->normalizeCacheKey(sha1($payload));
    }

    private function normalizeCacheData(array $data): ?array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            if ($key === '_cache') {
                continue;
            }

            $normalizedValue = $this->normalizeCacheValue($value);
            if ($normalizedValue === null && $value !== null) {
                return null;
            }

            $normalized[$key] = $normalizedValue;
        }

        return $normalized;
    }

    private function normalizeCacheValue(mixed $value): mixed
    {
        if (is_null($value) || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $itemKey => $itemValue) {
                $normalizedItem = $this->normalizeCacheValue($itemValue);
                if ($normalizedItem === null && $itemValue !== null) {
                    return null;
                }
                $normalized[$itemKey] = $normalizedItem;
            }

            return $normalized;
        }

        return null;
    }

    private function normalizeCacheKey(string $key): string
    {
        return 'templates.' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key);
    }
}
