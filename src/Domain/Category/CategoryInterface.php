<?php

declare(strict_types=1);

namespace App\Domain\Category;

interface CategoryInterface
{
    public function getId(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function getParent(): ?CategoryInterface;

    public function setParent(?CategoryInterface $parent): void;
}
