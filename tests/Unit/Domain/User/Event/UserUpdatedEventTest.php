<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Event;

use App\Domain\Shared\Event\DomainEvent;
use App\Domain\User\Event\UserUpdatedEvent;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserUpdatedEventTest extends TestCase
{
    public function testCollectsNormalizedChanges(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getId')->willReturn('user-789');
        $user->method('getEmail')->willReturn('demo@example.com');

        $occurredAt = new DateTimeImmutable('2024-03-03T15:00:00+00:00');

        $event = UserUpdatedEvent::fromUser($user, ['Email', 'roles', 'email', 'Status'], $occurredAt);

        self::assertInstanceOf(DomainEvent::class, $event);
        self::assertSame('user-789', $event->getUserId());
        self::assertSame('demo@example.com', $event->getEmail());
        self::assertSame(['email', 'roles', 'status'], $event->getChanges());
        self::assertSame($occurredAt, $event->getOccurredAt());
    }
}
