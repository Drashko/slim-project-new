<?php

declare(strict_types=1);

namespace App\Domain\Ad;

use App\Domain\User\UserInterface;
use DateTimeImmutable;

interface AdInterface
{
    public function getId(): string;

    public function getOwner(): UserInterface;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    /**
     * @return string[]
     */
    public function getImages(): array;

    /**
     * @param string[] $images
     */
    public function setImages(array $images): void;

    public function getCategory(): string;

    public function setCategory(string $category): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
