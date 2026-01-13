<?php

declare(strict_types=1);

namespace App\Integration\Repository;

use App\Domain\Shared\DomainException;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /**
     * @var array<string,UserInterface>
     */
    private array $usersById = [];

    /**
     * @var array<string,UserInterface>
     */
    private array $usersByEmail = [];

    /**
     * @param UserInterface[] $seed
     */
    public function __construct(array $seed = [])
    {
        foreach ($seed as $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            $this->store($user);
        }
    }

    public function add(UserInterface $user): void
    {
        $existing = $this->findByEmail($user->getEmail());
        if ($existing !== null) {
            throw new DomainException('A user with this email already exists.');
        }

        $this->store($user);
    }

    public function remove(UserInterface $user): void
    {
        unset($this->usersById[$user->getId()]);
        unset($this->usersByEmail[strtolower($user->getEmail())]);
    }

    public function find(string $id): ?UserInterface
    {
        return $this->usersById[$id] ?? null;
    }

    public function findByEmail(string $email): ?UserInterface
    {
        $normalized = strtolower(trim($email));

        return $this->usersByEmail[$normalized] ?? null;
    }

    public function flush(): void
    {
        // No-op for in-memory storage.
    }

    /**
     * @return UserInterface[]
     */
    public function all(): array
    {
        return array_values($this->usersById);
    }

    private function store(UserInterface $user): void
    {
        $this->usersById[$user->getId()] = $user;
        $this->usersByEmail[strtolower($user->getEmail())] = $user;
    }
}
