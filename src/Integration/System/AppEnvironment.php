<?php

declare(strict_types=1);

namespace App\Integration\System;

final class AppEnvironment
{
    public function __construct(private string $name)
    {
        $this->name = strtolower($this->name);
    }

    public function isProduction(): bool
    {
        return $this->name === 'prod';
    }

    public function isDevelopment(): bool
    {
        return $this->name === 'dev';
    }

    public function isTest(): bool
    {
        return $this->name === 'test';
    }

    public function name(): string
    {
        return $this->name;
    }
}
