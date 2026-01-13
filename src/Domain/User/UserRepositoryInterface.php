<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepositoryInterface
{
    public function add(UserInterface $user): void;

    public function remove(UserInterface $user): void;

    public function find(string $id): ?UserInterface;

    public function findByEmail(string $email): ?UserInterface;

    public function flush(): void;

    /**
     * @return UserInterface[]
     */
    public function all(): array;
}
