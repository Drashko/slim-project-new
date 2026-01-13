<?php

declare(strict_types=1);

namespace App\Feature\Ad\Handler;

use App\Domain\Ad\AdInterface;
use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Ad\Command\UpdateAdCommand;

final readonly class UpdateAdHandler
{
    public function __construct(private AdRepositoryInterface $adRepository)
    {
    }

    public function handle(UpdateAdCommand $command): AdInterface
    {
        $ad = $this->adRepository->find($command->getAdId());
        if (!$ad instanceof AdInterface) {
            throw new DomainException('Advertisement not found.');
        }

        $ad->setTitle($command->getTitle());
        $ad->setDescription($command->getDescription());
        $ad->setCategory($command->getCategory());
        $ad->setStatus($command->getStatus());

        if ($command->getImages() !== null) {
            $ad->setImages($command->getImages());
        }

        $this->adRepository->flush();

        return $ad;
    }
}
