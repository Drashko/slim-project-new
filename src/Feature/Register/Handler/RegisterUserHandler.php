<?php

declare(strict_types=1);

namespace App\Feature\Register\Handler;

use App\Feature\Register\Command\RegisterUserCommand;

use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(RegisterUserCommand $command): string
    {
        $existing = $this->userRepository->findByEmail($command->getEmail());
        if ($existing !== null) {
            throw new DomainException('User with this email already exists.');
        }

        $user = new User($command->getEmail(), $command->getPassword(), $command->getRoles());
        $this->userRepository->add($user);
        $this->userRepository->flush();

        $this->eventDispatcher->dispatch(UserCreatedEvent::fromUser($user));

        return $user->getId();
    }
}
