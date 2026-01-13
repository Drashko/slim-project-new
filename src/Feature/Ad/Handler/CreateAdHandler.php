<?php

declare(strict_types=1);

namespace App\Feature\Ad\Handler;

use App\Domain\Ad\Ad;
use App\Domain\Ad\AdInterface;
use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Domain\User\User;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Ad\Command\CreateAdCommand;

final readonly class CreateAdHandler
{
    public function __construct(
        private AdRepositoryInterface $adRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(CreateAdCommand $command): AdInterface
    {
        $user = $this->userRepository->find($command->getUserId());
        if (!$user instanceof UserInterface) {
            throw new DomainException('Unable to find the owner of this ad.');
        }

        if (!$user instanceof User) {
            throw new DomainException('Ad owner must be a registered user.');
        }

        $ad = new Ad(
            $user,
            $command->getTitle(),
            $command->getDescription(),
            $command->getCategory(),
            $command->getImages(),
            $command->getStatus()
        );

        $this->adRepository->add($ad);
        $this->adRepository->flush();

        return $ad;
    }
}
