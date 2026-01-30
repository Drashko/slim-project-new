<?php

declare(strict_types=1);

namespace App\Domain\Session;

use DateTimeImmutable;
class SessionRecord
{
    private string $id;

    private string $type;

    private array $data = [];

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $id, string $type, array $data = [])
    {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
