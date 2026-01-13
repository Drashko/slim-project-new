<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

use DateTimeImmutable;

interface DomainEvent
{
    public function getOccurredAt(): DateTimeImmutable;
}
