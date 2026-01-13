<?php

declare(strict_types=1);

namespace App\Web\Admin\Service;

use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Provides a deterministic team directory based on the demo data feed.
 */
final readonly class UserService
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $extended = [];
        foreach ($this->users->all() as $index => $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            $extended[] = $this->mapUser($user, $index);
        }

        return $extended;
    }

    public function find(string $id): ?array
    {
        $user = $this->users->find($id);
        if (!$user instanceof UserInterface) {
            return null;
        }

        return $this->mapUser($user, 0);
    }

    /**
     * @param array<int, array<string, mixed>> $directory
     * @return array<int, string>
     */
    public function roles(array $directory): array
    {
        $roles = array_values(array_unique(array_map(
            static fn(array $member): string => (string) ($member['role'] ?? 'Team'),
            $directory
        )));
        sort($roles);

        return $roles;
    }

    /**
     * @param array<int, array<string, mixed>> $directory
     * @return array<int, string>
     */
    public function statuses(array $directory): array
    {
        $statuses = array_values(array_unique(array_map(
            static fn(array $member): string => (string) ($member['status'] ?? 'Active'),
            $directory
        )));
        sort($statuses);

        return $statuses;
    }

    private function mapUser(UserInterface $user, int $index): array
    {
        $roles = $user->getRoles();
        $createdAt = $user->getCreatedAt();
        $updatedAt = $user->getUpdatedAt();
        $status = trim($user->getStatus());
        if ($status === '') {
            $status = $this->resolveStatus($createdAt, $updatedAt, $index);
        }

        return [
            'id' => $user->getId(),
            'name' => $this->nameFromEmail($user->getEmail()),
            'email' => $user->getEmail(),
            'role' => $roles[0] ?? 'Team',
            'status' => $status,
            'last_login' => $updatedAt->format('Y-m-d H:i'),
            'permissions' => $roles,
            'created_at' => $createdAt->format(DateTimeInterface::ATOM),
            'updated_at' => $updatedAt->format(DateTimeInterface::ATOM),
        ];
    }

    private function nameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $parts = array_filter(preg_split('/[._-]+/', $localPart));

        return $parts === [] ? $email : implode(' ', array_map(static fn(string $part): string => ucfirst(strtolower($part)), $parts));
    }

    private function resolveStatus(DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt, int $index): string
    {
        $recentActivityThreshold = new DateTimeImmutable('-7 days');
        if ($updatedAt >= $recentActivityThreshold) {
            return 'Active';
        }

        if ($createdAt >= new DateTimeImmutable('-14 days')) {
            return 'Pending';
        }

        return $index % 2 === 0 ? 'Pending' : 'Active';
    }
}
