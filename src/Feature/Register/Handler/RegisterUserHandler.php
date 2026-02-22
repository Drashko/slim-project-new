<?php

declare(strict_types=1);

namespace App\Feature\Register\Handler;

use App\Domain\Role\RoleCatalog;
use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Register\Command\RegisterUserCommand;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher,
        private RoleCatalog $roleCatalog
    )
    {
    }

    public function handle(RegisterUserCommand $command): string
    {
        $existing = $this->userRepository->findByEmail($command->getEmail());
        if ($existing !== null) {
            throw new DomainException('User with this email already exists.');
        }

        $roles = $this->roleCatalog->assertAllowed($command->getRoles());
        $user = new User($command->getEmail(), $command->getPassword(), $roles);
        $this->userRepository->add($user);
        $this->userRepository->flush();

        $this->eventDispatcher->dispatch(UserCreatedEvent::fromUser($user));

        return $user->getId();
    }
}
