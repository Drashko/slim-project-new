<?php

namespace App\Integration\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggerFactory
{
    /**
     * @var array<string,mixed>
     */
    private array $settings;

    /**
     * @var array<int,StreamHandler>
     */
    private array $handlers = [];

    /**
     * @param array<string,mixed> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function addFileHandler(string $filename): self
    {
        $path = $this->settings['path'] ?? sys_get_temp_dir();
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $level = $this->settings['level'] ?? Level::Debug;
        $permission = $this->settings['file_permission'] ?? null;
        $stream = new StreamHandler(
            $path . DIRECTORY_SEPARATOR . $filename,
            $level,
            true,
            $permission
        );

        $this->handlers[] = $stream;

        return $this;
    }

    public function createLogger(?string $name = null): Logger
    {
        $loggerName = $name ?? $this->settings['name'] ?? 'app';
        $logger = new Logger($loggerName);

        if (empty($this->handlers)) {
            $defaultFilename = $this->settings['filename'] ?? ($loggerName . '.log');
            $this->addFileHandler($defaultFilename);
        }

        foreach ($this->handlers as $handler) {
            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
