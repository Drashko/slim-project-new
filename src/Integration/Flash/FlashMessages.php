<?php

declare(strict_types=1);

namespace App\Integration\Flash;

use App\Integration\Session\AdminSessionInterface;
use App\Integration\Session\PublicSessionInterface;
use App\Integration\Session\SessionInterface;

final class FlashMessages
{
    private const SESSION_KEY = '_flash_messages';

    public function __construct(
        private readonly PublicSessionInterface $publicSession,
        private readonly AdminSessionInterface $adminSession
    ) {
    }

    public function addMessage(string $type, string $message): void
    {
        $session = $this->currentSession();
        $messages = $this->readMessages($session);
        $messages[$type][] = $message;
        $session->set(self::SESSION_KEY, $messages);
    }

    /**
     * @return array<string, list<string>>
     */
    public function getMessages(): array
    {
        $session = $this->currentSession();
        $messages = $this->readMessages($session);
        $session->delete(self::SESSION_KEY);

        return $messages;
    }

    private function currentSession(): SessionInterface
    {
        $path = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url((string) $path, PHP_URL_PATH);
        $segments = array_values(array_filter(
            explode('/', trim((string) $path, '/')),
            static fn(string $segment): bool => $segment !== ''
        ));

        if ($segments !== [] && $this->isLocaleSegment($segments[0])) {
            array_shift($segments);
        }

        if ($segments !== [] && strtolower($segments[0]) === 'admin') {
            return $this->adminSession;
        }

        return $this->publicSession;
    }

    /**
     * @return array<string, list<string>>
     */
    private function readMessages(SessionInterface $session): array
    {
        $messages = $session->get(self::SESSION_KEY);
        if (!is_array($messages)) {
            return [];
        }

        $normalized = [];
        foreach ($messages as $type => $values) {
            if (!is_string($type) || !is_array($values)) {
                continue;
            }

            $normalized[$type] = array_values(array_filter(array_map(
                static fn(mixed $value): ?string => is_scalar($value) ? (string) $value : null,
                $values
            ), static fn(?string $value): bool => $value !== null && $value !== ''));
        }

        return $normalized;
    }

    private function isLocaleSegment(string $segment): bool
    {
        return (bool) preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/i', $segment);
    }
}
