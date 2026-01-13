<?php

declare(strict_types=1);

namespace App\Feature\Ad\Query;

final readonly class GetAdQuery
{
    public function __construct(private string $adId)
    {
    }

    public function getAdId(): string
    {
        return $this->adId;
    }
}
