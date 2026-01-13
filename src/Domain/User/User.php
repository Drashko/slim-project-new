<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $passwordHash;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'Active';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $email, string $plainPassword, array $roles = ['ROLE_USER'], string $status = 'Active')
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->setEmail($email);
        $this->changePassword($plainPassword);
        $this->setRoles($roles);
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

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $normalized = [];
        foreach ($roles as $role) {
            $normalized[] = strtoupper((string) $role);
        }

        $this->roles = array_values(array_unique($normalized));
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
}
