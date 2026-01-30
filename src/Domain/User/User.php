<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class User implements UserInterface
{
    private string $id;

    private string $email;

    private string $passwordHash;

    /**
     * @var string[]
     */
    private array $roles = [];

    private int $rolesVersion = 1;

    private string $status = 'Active';

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $email, string $plainPassword, array $roles = ['ROLE_USER'], string $status = 'Active')
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->setEmail($email);
        $this->changePassword($plainPassword);
        $this->roles = $this->normalizeRoles($roles);
        $this->setStatus($status);
        $this->createdAt = new DateTimeImmutable('now');
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }

        $this->email = $email;
        $this->touch();
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function changePassword(string $plainPassword): void
    {
        if ($plainPassword === '') {
            throw new \InvalidArgumentException('Password cannot be empty.');
        }

        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('Unable to hash password.');
        }

        $this->passwordHash = $hash;
        $this->touch();
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getRolesVersion(): int
    {
        return $this->rolesVersion;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $normalized = $this->normalizeRoles($roles);
        if ($normalized === $this->roles) {
            return;
        }

        $this->roles = $normalized;
        $this->rolesVersion++;
        $this->touch();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $normalized = ucfirst(strtolower(trim($status)));
        if ($normalized === '') {
            throw new \InvalidArgumentException('Status cannot be empty.');
        }

        $this->status = $normalized;
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            $value = strtoupper(trim((string) $role));
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }
}
