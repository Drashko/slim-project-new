<?php

declare(strict_types=1);

namespace App\Feature\User\Handler;

use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\User\Query\GetUserQuery;

final readonly class GetUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(GetUserQuery $query): ?UserInterface
    {
        return $this->userRepository->find($query->getUserId());
    }
}
