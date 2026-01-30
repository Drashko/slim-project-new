<?php

declare(strict_types=1);

namespace App\Domain\Ad;

use App\Domain\User\User;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class Ad implements AdInterface
{
    private string $id;

    private User $owner;

    private string $title;

    private string $description;

    /**
     * @var string[]
     */
    private array $images = [];

    private string $category;

    private string $status = 'Pending';

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        User $owner,
        string $title,
        string $description,
        string $category,
        array $images = [],
        string $status = 'Pending'
    ) {
        $this->id = Uuid::v4()->toRfc4122();
        $this->owner = $owner;
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setCategory($category);
        $this->setImages($images);
        $this->setStatus($status);
        $this->createdAt = new DateTimeImmutable('now');
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): UserInterface
    {
        return $this->owner;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $normalized = trim($title);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }

        $this->title = $normalized;
        $this->touch();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $normalized = trim($description);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }

        $this->description = $normalized;
        $this->touch();
    }

    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param string[] $images
     */
    public function setImages(array $images): void
    {
        $normalized = [];
        foreach ($images as $image) {
            $candidate = trim((string) $image);
            if ($candidate !== '') {
                $normalized[] = $candidate;
            }
        }

        $this->images = array_values(array_unique($normalized));
        $this->touch();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $normalized = trim($category);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Category is required.');
        }

        $this->category = $normalized;
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
