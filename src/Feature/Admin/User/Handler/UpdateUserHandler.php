<?php

declare(strict_types=1);

namespace App\Feature\Admin\User\Handler;

use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Admin\User\Command\UpdateUserCommand;

final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(UpdateUserCommand $command): UserInterface
    {
        $user = $this->userRepository->find($command->getUserId());
        if (!$user instanceof UserInterface) {
            throw new DomainException('User not found.');
        }

        $changes = [];

        $email = $command->getEmail();
        if ($email !== null) {
            $normalizedEmail = trim($email);
            if ($normalizedEmail === '') {
                throw new DomainException('Email is required.');
            }

            $existing = $this->userRepository->findByEmail($normalizedEmail);
            if ($existing instanceof UserInterface && $existing->getId() !== $user->getId()) {
                throw new DomainException('User with this email already exists.');
            }

            if (strcasecmp($user->getEmail(), $normalizedEmail) !== 0) {
                $user->setEmail($normalizedEmail);
                $changes[] = 'email';
            }
        }

        $password = $command->getPassword();
        if ($password !== null) {
            if (trim($password) === '') {
                throw new DomainException('Password is required.');
            }

            $user->changePassword($password);
            $changes[] = 'password';
        }

        $roles = $command->getRoles();
        if ($roles !== null) {
            if ($this->rolesDiffer($user->getRoles(), $roles)) {
                $user->setRoles($roles);
                $changes[] = 'roles';
            }
        }

        $status = $command->getStatus();
        if ($status !== null) {
            if (strcasecmp($user->getStatus(), $status) !== 0) {
                $user->setStatus($status);
                $changes[] = 'status';
            }
        }

        $this->userRepository->flush();

        if ($changes !== []) {
            $this->eventDispatcher->dispatch(UserUpdatedEvent::fromUser($user, $changes));
        }

        return $user;
    }

    /**
     * @param string[] $current
     * @param string[] $updated
     */
    private function rolesDiffer(array $current, array $updated): bool
    {
        return array_values(array_unique(array_map('strtoupper', $current)))
            !== array_values(array_unique(array_map('strtoupper', $updated)));
    }
}
