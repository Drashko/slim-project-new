<?php

declare(strict_types=1);

namespace App\Domain\Session;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_sessions')]
class SessionRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 64)]
    private string $id;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 16)]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column(type: 'datetime_immutable')]
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
