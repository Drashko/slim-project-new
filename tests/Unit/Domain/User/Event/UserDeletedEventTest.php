<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Event;

use App\Domain\User\Event\UserDeletedEvent;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserDeletedEventTest extends TestCase
{
    public function testItExposesConstructorValues(): void
    {
        $occurredAt = new DateTimeImmutable('2024-02-03T04:05:06+00:00');
        $event = new UserDeletedEvent('user-789', 'removed@example.com', $occurredAt);

        self::assertSame('user-789', $event->getUserId());
        self::assertSame('removed@example.com', $event->getEmail());
        self::assertSame($occurredAt, $event->getOccurredAt());
    }

    public function testItCanBeCreatedFromUserInterface(): void
    {
        /** @var UserInterface&MockObject $user */
        $user = $this->createMock(UserInterface::class);
        $user->method('getId')->willReturn('deleted-id');
        $user->method('getEmail')->willReturn('deleted@example.com');

        $event = UserDeletedEvent::fromUser($user);

        self::assertSame('deleted-id', $event->getUserId());
        self::assertSame('deleted@example.com', $event->getEmail());
        self::assertInstanceOf(DateTimeImmutable::class, $event->getOccurredAt());
    }
}
