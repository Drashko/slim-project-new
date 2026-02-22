<?php

declare(strict_types=1);

namespace App\Feature\User\Handler;

use App\Domain\Role\RoleCatalog;
use App\Domain\Shared\DomainException;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Feature\User\Command\AssignRoleCommand;

final readonly class AssignRoleHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DomainEventDispatcherInterface $eventDispatcher,
        private RoleCatalog $roleCatalog
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
            $user->setRoles($this->roleCatalog->assertAllowed($command->getRoles()));
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
        return array_values(array_unique(array_map('strtolower', $current)))
            !== array_values(array_unique(array_map('strtolower', $updated)));
    }
}
