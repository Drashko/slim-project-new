<?php

declare(strict_types=1);

namespace App\Feature\Admin\User\Handler;

use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\Admin\User\Command\AssignRoleCommand;

final readonly class AssignRoleHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(AssignRoleCommand $command): UserInterface
    {
        $user = $this->userRepository->find($command->getUserId());
        if ($user === null) {
            throw new DomainException('User not found.');
        }

        if ($this->rolesDiffer($user->getRoles(), $command->getRoles())) {
            $user->setRoles($command->getRoles());
            $this->userRepository->flush();

            $this->eventDispatcher->dispatch(UserUpdatedEvent::fromUser($user, ['roles']));
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
