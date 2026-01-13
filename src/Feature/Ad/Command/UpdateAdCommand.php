<?php

declare(strict_types=1);

namespace App\Feature\Ad\Command;

final readonly class UpdateAdCommand
{
    /**
     * @param string[]|null $images
     */
    public function __construct(
        private string $adId,
        private string $title,
        private string $description,
        private string $category,
        private string $status,
        private ?array $images = null
    ) {
    }

    public function getAdId(): string
    {
        return $this->adId;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string[]|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }
}
