<?php

declare(strict_types=1);

namespace App\Domain\Category;

interface CategoryRepositoryInterface
{
    public function add(CategoryInterface $category): void;

    public function remove(CategoryInterface $category): void;

    public function findById(string $id): ?CategoryInterface;

    /**
     * @return CategoryInterface[]
     */
    public function all(): array;

    public function flush(): void;
}
