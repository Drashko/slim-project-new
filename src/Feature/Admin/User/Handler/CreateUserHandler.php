<?php

declare(strict_types=1);

namespace App\Feature\Admin\User\Handler;

use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\User;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Admin\User\Command\CreateUserCommand;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(CreateUserCommand $command): UserInterface
    {
        $email = trim($command->getEmail());
        $password = trim($command->getPassword());

        if ($email === '') {
            throw new DomainException('Email is required.');
        }

        if ($password === '') {
            throw new DomainException('Password is required.');
        }

        $existing = $this->userRepository->findByEmail($email);
        if ($existing instanceof UserInterface) {
            throw new DomainException('User with this email already exists.');
        }

        $user = new User($email, $password, $command->getRoles(), $command->getStatus());
        $this->userRepository->add($user);
        $this->userRepository->flush();

        $this->eventDispatcher->dispatch(UserCreatedEvent::fromUser($user));

        return $user;
    }
}
