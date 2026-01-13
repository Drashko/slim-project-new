<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use RuntimeException;

final class Engine
{
    private string $directory;

    private string $fileExtension;

    /**
     * @var array<string, string>
     */
    private array $folders = [];

    /**
     * @var array<string, mixed>
     */
    private array $sharedData = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $templateData = [];

    public function __construct(string $directory, string $fileExtension = 'php')
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $this->fileExtension = ltrim($fileExtension, '.');
    }

    public function addFolder(string $name, string $directory): self
    {
        $this->folders[$name] = rtrim($directory, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * @param string|string[]|null $templates
     */
    public function addData(array $data, string|array|null $templates = null): self
    {
        if ($templates === null) {
            $this->sharedData = array_merge($this->sharedData, $data);

            return $this;
        }

        foreach ((array) $templates as $template) {
            $this->templateData[$template] = array_merge($this->templateData[$template] ?? [], $data);
        }

        return $this;
    }

    public function render(string $name, array $data = []): string
    {
        $templateData = array_merge($this->sharedData, $this->templateData[$name] ?? [], $data);
        $template = new Template($this, $name, $templateData);

        return $template->render();
    }

    public function renderLayout(string $name, array $data, array $sections): string
    {
        $templateData = array_merge($this->sharedData, $this->templateData[$name] ?? [], $data);
        $template = new Template($this, $name, $templateData, $sections, true);

        return $template->render();
    }

    public function resolvePath(string $name): string
    {
        $directory = $this->directory;
        $templateName = $name;

        if (str_contains($name, '::')) {
            [$folder, $templateName] = explode('::', $name, 2);
            if ($folder !== '') {
                $directory = $this->folders[$folder] ?? ($this->directory . DIRECTORY_SEPARATOR . $folder);
            }
        } elseif (str_contains($name, '/')) {
            $segments = explode('/', $name);
            $templateName = array_pop($segments);
            if ($segments !== []) {
                $directory = $this->directory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments);
            }
        }

        if (!str_ends_with($templateName, '.' . $this->fileExtension)) {
            $templateName .= '.' . $this->fileExtension;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $templateName;

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Template "%s" was not found at path "%s".', $name, $path));
        }

        return $path;
    }
}
