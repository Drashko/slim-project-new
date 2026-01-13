<?php

declare(strict_types=1);

namespace App\Feature\Ad\Handler;

use App\Domain\Ad\AdInterface;
use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Ad\Query\GetAdQuery;

final readonly class GetAdHandler
{
    public function __construct(private AdRepositoryInterface $adRepository)
    {
    }

    public function handle(GetAdQuery $query): AdInterface
    {
        $ad = $this->adRepository->find($query->getAdId());
        if (!$ad instanceof AdInterface) {
            throw new DomainException('Advertisement not found.');
        }

        return $ad;
    }
}
