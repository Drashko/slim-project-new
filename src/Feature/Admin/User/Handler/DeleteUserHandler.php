<?php

declare(strict_types=1);

namespace App\Feature\Admin\User\Handler;

use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserDeletedEvent;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Admin\User\Command\DeleteUserCommand;

final readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $user = $this->userRepository->find($command->getUserId());
        if (!$user instanceof UserInterface) {
            throw new DomainException('User not found.');
        }

        $this->userRepository->remove($user);
        $this->userRepository->flush();

        $this->eventDispatcher->dispatch(UserDeletedEvent::fromUser($user));
    }
}
