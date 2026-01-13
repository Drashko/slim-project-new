<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\Event\DomainEventWithPayload;
use App\Domain\User\UserInterface;
use DateTimeImmutable;

final readonly class UserCreatedEvent implements DomainEventWithPayload
{
    public function __construct(
        private string            $userId,
        private string            $email,
        private DateTimeImmutable $occurredAt
    ) {
    }

    public static function fromUser(UserInterface $user, ?DateTimeImmutable $occurredAt = null): self
    {
        return new self(
            $user->getId(),
            $user->getEmail(),
            $occurredAt ?? new DateTimeImmutable('now'),
        );
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAggregateId(): string
    {
        return $this->userId;
    }

    public function getAggregateType(): string
    {
        return 'user';
    }

    public function getEventType(): string
    {
        return 'user.created';
    }

    public function getPayload(): array
    {
        return [
            'userId' => $this->userId,
            'email' => $this->email,
        ];
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
