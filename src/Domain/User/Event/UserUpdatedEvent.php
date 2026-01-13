<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\Event\DomainEventWithPayload;
use App\Domain\User\UserInterface;
use DateTimeImmutable;

final readonly class UserUpdatedEvent implements DomainEventWithPayload
{
    /**
     * @param string[] $changes
     */
    public function __construct(
        private string            $userId,
        private string            $email,
        private array             $changes,
        private DateTimeImmutable $occurredAt
    ) {
    }

    /**
     * @param string[] $changes
     */
    public static function fromUser(UserInterface $user, array $changes, ?DateTimeImmutable $occurredAt = null): self
    {
        $normalizedChanges = [];
        foreach ($changes as $change) {
            $change = strtolower(trim((string) $change));
            if ($change !== '') {
                $normalizedChanges[$change] = $change;
            }
        }

        return new self(
            $user->getId(),
            $user->getEmail(),
            array_values($normalizedChanges),
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

    /**
     * @return string[]
     */
    public function getChanges(): array
    {
        return $this->changes;
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
        return 'user.updated';
    }

    public function getPayload(): array
    {
        return [
            'userId' => $this->userId,
            'email' => $this->email,
            'changes' => $this->changes,
        ];
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
