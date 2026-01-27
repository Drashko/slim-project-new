<?php

declare(strict_types=1);

namespace App\Integration\Session;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class DatabaseSessionStore
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function load(string $id, string $type): array
    {
        try {
            $row = $this->connection->fetchAssociative(
                'SELECT data FROM app_sessions WHERE id = ? AND type = ?',
                [$id, $type]
            );
        } catch (Exception) {
            return [];
        }

        if ($row === false || !array_key_exists('data', $row)) {
            return [];
        }

        $decoded = json_decode((string) $row['data'], true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(string $id, string $type, array $data): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return;
        }

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        try {
            $affected = $this->connection->executeStatement(
                'UPDATE app_sessions SET data = ?, updated_at = ? WHERE id = ? AND type = ?',
                [$payload, $now, $id, $type]
            );

            if ($affected === 0) {
                $this->connection->executeStatement(
                    'INSERT INTO app_sessions (id, type, data, updated_at) VALUES (?, ?, ?, ?)',
                    [$id, $type, $payload, $now]
                );
            }
        } catch (Exception) {
        }
    }

    public function delete(string $id, string $type): void
    {
        try {
            $this->connection->executeStatement(
                'DELETE FROM app_sessions WHERE id = ? AND type = ?',
                [$id, $type]
            );
        } catch (Exception) {
        }
    }
}
