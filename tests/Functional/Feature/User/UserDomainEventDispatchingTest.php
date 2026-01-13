<?php

declare(strict_types=1);

namespace Tests\Functional\Feature\User;

use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\Event\UserDeletedEvent;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Integration\Repository\InMemoryUserRepository;
use App\Feature\Admin\User\AssignRole\AssignRoleCommand;
use App\Feature\Admin\User\AssignRole\AssignRoleHandler;
use App\Feature\Admin\User\CreateUser\CreateUserCommand;
use App\Feature\Admin\User\CreateUser\CreateUserHandler;
use App\Feature\Admin\User\DeleteUser\DeleteUserCommand;
use App\Feature\Admin\User\DeleteUser\DeleteUserHandler;
use App\Feature\Admin\User\UpdateUser\UpdateUserCommand;
use App\Feature\Admin\User\UpdateUser\UpdateUserHandler;
use PHPUnit\Framework\TestCase;

final class UserDomainEventDispatchingTest extends TestCase
{
    public function testLifecycleHandlersDispatchDomainEvents(): void
    {
        $dispatcher = new InMemoryDomainEventDispatcher();
        $events = [];

        $dispatcher->addListener(UserCreatedEvent::class, static function (UserCreatedEvent $event) use (&$events): void {
            $events['created'][] = $event;
        });

        $dispatcher->addListener(UserUpdatedEvent::class, static function (UserUpdatedEvent $event) use (&$events): void {
            $events['updated'][] = $event;
        });

        $dispatcher->addListener(UserDeletedEvent::class, static function (UserDeletedEvent $event) use (&$events): void {
            $events['deleted'][] = $event;
        });

        $repository = new InMemoryUserRepository();

        $createHandler = new CreateUserHandler($repository, $dispatcher);
        $updateHandler = new UpdateUserHandler($repository, $dispatcher);
        $assignRoleHandler = new AssignRoleHandler($repository, $dispatcher);
        $deleteHandler = new DeleteUserHandler($repository, $dispatcher);

        $user = $createHandler->handle(new CreateUserCommand('listener@example.com', 'secret', ['ROLE_API']));
        $updateHandler->handle(new UpdateUserCommand($user->getId(), 'updated@example.com', 'new-secret', ['ROLE_MANAGER'], 'Inactive'));
        $assignRoleHandler->handle(new AssignRoleCommand($user->getId(), ['ROLE_MANAGER', 'ROLE_USER']));
        $deleteHandler->handle(new DeleteUserCommand($user->getId()));

        self::assertCount(1, $events['created'] ?? []);
        self::assertCount(2, $events['updated'] ?? []);
        self::assertCount(1, $events['deleted'] ?? []);

        $firstUpdate = $events['updated'][0];
        $secondUpdate = $events['updated'][1];

        self::assertSame(['email', 'password', 'roles', 'status'], $firstUpdate->getChanges());
        self::assertSame(['roles'], $secondUpdate->getChanges());
        self::assertSame($user->getId(), $events['deleted'][0]->getUserId());
    }
}
