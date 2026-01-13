<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use DateInterval;
use DateTimeImmutable;

final class Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    public function fromNow(string $intervalSpec): DateTimeImmutable
    {
        return $this->now()->add(new DateInterval($intervalSpec));
    }
}
