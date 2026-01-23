<?php

declare(strict_types=1);

namespace App\Integration\Session;

use InvalidArgumentException;

class CookieSession implements SessionInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];
    private ?string $id;
    private bool $loaded = false;

    public function __construct(
        private readonly DatabaseSessionStore $store,
        private readonly string $cookieName,
        private readonly string $cookiePath,
        private readonly string $type,
        private readonly int $ttl,
        private readonly bool $secure,
        private readonly bool $httpOnly,
        private readonly string $sameSite
    ) {
        if ($this->cookieName === '') {
            throw new InvalidArgumentException('Cookie name cannot be empty.');
        }

        $cookieValue = $_COOKIE[$this->cookieName] ?? null;
        $this->id = is_string($cookieValue) && $cookieValue !== '' ? $cookieValue : null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->load();

        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->load();
        $this->data[$key] = $value;
        $this->persist();
    }

    public function delete(string $key): void
    {
        $this->load();
        unset($this->data[$key]);
        $this->persist();
    }

    public function remove(string $key): void
    {
        $this->delete($key);
    }

    public function clear(): void
    {
        $this->load();
        $this->data = [];

        if ($this->id !== null) {
            $this->store->delete($this->id, $this->type);
        }

        $this->clearCookie();
        $this->id = null;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        if ($this->id !== null) {
            $this->data = $this->store->load($this->id, $this->type);
        }

        $this->loaded = true;
    }

    private function persist(): void
    {
        if ($this->id === null) {
            $this->id = $this->generateId();
        }

        $this->store->save($this->id, $this->type, $this->data);
        $this->setCookie($this->id);
    }

    private function generateId(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function setCookie(string $value): void
    {
        setcookie($this->cookieName, $value, [
            'expires' => time() + $this->ttl,
            'path' => $this->cookiePath,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);
    }

    private function clearCookie(): void
    {
        setcookie($this->cookieName, '', [
            'expires' => time() - 3600,
            'path' => $this->cookiePath,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ]);
    }
}
