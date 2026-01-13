<?php

declare(strict_types=1);

namespace App\Feature\Ad\Handler;

use App\Domain\Ad\AdInterface;
use App\Domain\Ad\AdRepositoryInterface;
use App\Feature\Ad\Query\ListAdsQuery;

final readonly class ListAdsHandler
{
    public function __construct(private AdRepositoryInterface $adRepository)
    {
    }

    /**
     * @return AdInterface[]
     */
    public function handle(ListAdsQuery $query): array
    {
        $userId = $query->getUserId();

        if ($userId !== null && !$query->hasFilters()) {
            return $this->adRepository->findByUser($userId);
        }

        if ($query->hasFilters()) {
            return $this->adRepository->findByFilters(
                $query->getCategory(),
                $query->getStatus(),
                $query->getUserFilter(),
                $query->getFromDate(),
                $query->getToDate(),
                $userId
            );
        }

        return $this->adRepository->all();
    }
}
