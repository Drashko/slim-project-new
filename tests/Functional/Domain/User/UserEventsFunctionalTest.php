<?php

declare(strict_types=1);

namespace Tests\Functional\Domain\User;

use App\Domain\Shared\Event\DomainEvent;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\Event\UserDeletedEvent;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Domain\User\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserEventsFunctionalTest extends TestCase
{
    public function testEventsCaptureStateFromDomainEntity(): void
    {
        $user = new User('Functional@example.com', 'super-secret');

        $createdAt = new DateTimeImmutable('2024-03-01T10:15:00+00:00');
        $updatedAt = $createdAt->modify('+30 minutes');
        $deletedAt = $createdAt->modify('+1 hour');

        $createdEvent = UserCreatedEvent::fromUser($user, $createdAt);
        $updatedEvent = UserUpdatedEvent::fromUser($user, ['email', 'roles', 'status'], $updatedAt);
        $deletedEvent = UserDeletedEvent::fromUser($user, $deletedAt);

        self::assertInstanceOf(DomainEvent::class, $createdEvent);
        self::assertSame($user->getId(), $createdEvent->getUserId());
        self::assertSame($user->getEmail(), $createdEvent->getEmail());
        self::assertSame($createdAt, $createdEvent->getOccurredAt());

        self::assertInstanceOf(DomainEvent::class, $updatedEvent);
        self::assertSame($user->getId(), $updatedEvent->getUserId());
        self::assertSame($user->getEmail(), $updatedEvent->getEmail());
        self::assertSame(['email', 'roles', 'status'], $updatedEvent->getChanges());
        self::assertSame($updatedAt, $updatedEvent->getOccurredAt());

        self::assertInstanceOf(DomainEvent::class, $deletedEvent);
        self::assertSame($user->getId(), $deletedEvent->getUserId());
        self::assertSame($user->getEmail(), $deletedEvent->getEmail());
        self::assertSame($deletedAt, $deletedEvent->getOccurredAt());

        self::assertTrue($createdEvent->getOccurredAt() < $updatedEvent->getOccurredAt());
        self::assertTrue($updatedEvent->getOccurredAt() < $deletedEvent->getOccurredAt());
    }
}
