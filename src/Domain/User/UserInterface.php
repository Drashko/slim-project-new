<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserInterface
{
    public function getId(): string;

    public function getEmail(): string;

    public function setEmail(string $email): void;

    public function getPasswordHash(): string;

    public function changePassword(string $plainPassword): void;

    public function verifyPassword(string $plainPassword): bool;

    /**
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;
}
