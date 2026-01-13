<?php

declare(strict_types=1);

namespace App\Feature\Ad\Command;

final readonly class CreateAdCommand
{
    /**
     * @param string[] $images
     */
    public function __construct(
        private string $userId,
        private string $title,
        private string $description,
        private string $category,
        private array $images = [],
        private string $status = 'Pending'
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
