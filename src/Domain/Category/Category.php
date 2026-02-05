<?php

declare(strict_types=1);

namespace App\Domain\Category;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class Category implements CategoryInterface
{
    private string $id;

    private string $name;

    private ?Category $parent = null;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $name, ?CategoryInterface $parent = null)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->setName($name);
        $this->setParent($parent);
        $this->createdAt = new DateTimeImmutable('now');
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $normalized = trim($name);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Category name is required.');
        }

        $this->name = $normalized;
        $this->touch();
    }

    public function getParent(): ?CategoryInterface
    {
        return $this->parent;
    }

    public function setParent(?CategoryInterface $parent): void
    {
        if ($parent instanceof CategoryInterface && $parent->getId() === $this->id) {
            throw new \InvalidArgumentException('Category cannot be its own parent.');
        }

        $this->parent = $parent instanceof self ? $parent : null;
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
