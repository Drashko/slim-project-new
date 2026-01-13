<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use RuntimeException;

final class Template
{
    private Engine $engine;

    private string $name;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @var array<string, string>
     */
    private array $sections;

    /**
     * @var string[]
     */
    private array $sectionStack = [];

    private ?string $layoutName = null;

    /**
     * @var array<string, mixed>
     */
    private array $layoutData = [];

    private bool $isLayout;

    public function __construct(Engine $engine, string $name, array $data = [], array $sections = [], bool $isLayout = false)
    {
        $this->engine = $engine;
        $this->name = $name;
        $this->data = $data;
        $this->sections = $sections;
        $this->isLayout = $isLayout;
    }

    public function layout(string $name, array $data = []): void
    {
        $this->layoutName = $name;
        $this->layoutData = $data;
    }

    public function start(string $section): void
    {
        $this->sectionStack[] = $section;
        ob_start();
    }

    public function stop(): void
    {
        if ($this->sectionStack === []) {
            throw new RuntimeException('Cannot end a section without starting one.');
        }

        $section = array_pop($this->sectionStack);
        $this->sections[$section] = ob_get_clean() ?: '';
    }

    public function end(): void
    {
        $this->stop();
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function render(): string
    {
        $this->sectionStack = [];

        if (!$this->isLayout) {
            $this->sections = [];
        }

        $engine = $this->engine;

        $renderer = function () use ($engine): void {
            extract($this->data, EXTR_SKIP);
            $path = $engine->resolvePath($this->name);
            include $path;
        };

        ob_start();
        $renderer->call($this);
        $output = ob_get_clean() ?: '';

        if ($this->isLayout) {
            return $output;
        }

        if ($this->layoutName !== null) {
            if (!isset($this->sections['content'])) {
                $this->sections['content'] = $output;
            }

            $layoutData = array_merge($this->data, $this->layoutData);

            return $this->engine->renderLayout($this->layoutName, $layoutData, $this->sections);
        }

        return $output;
    }
}
