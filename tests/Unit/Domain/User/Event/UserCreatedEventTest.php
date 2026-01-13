<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\Event;

use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserCreatedEventTest extends TestCase
{
    public function testItExposesConstructorValues(): void
    {
        $occurredAt = new DateTimeImmutable('2024-01-02T03:04:05+00:00');
        $event = new UserCreatedEvent('user-123', 'tester@example.com', $occurredAt);

        self::assertSame('user-123', $event->getUserId());
        self::assertSame('tester@example.com', $event->getEmail());
        self::assertSame($occurredAt, $event->getOccurredAt());
    }

    public function testItCanBeCreatedFromUserInterface(): void
    {
        /** @var UserInterface&MockObject $user */
        $user = $this->createMock(UserInterface::class);
        $user->method('getId')->willReturn('generated-id');
        $user->method('getEmail')->willReturn('user@example.com');

        $event = UserCreatedEvent::fromUser($user);

        self::assertSame('generated-id', $event->getUserId());
        self::assertSame('user@example.com', $event->getEmail());
        self::assertInstanceOf(DateTimeImmutable::class, $event->getOccurredAt());
    }
}
