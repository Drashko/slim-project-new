<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\Category\Category;
use App\Domain\Category\CategoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class CategoryRepository implements CategoryRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Category::class);
    }

    public function add(CategoryInterface $category): void
    {
        $this->entityManager->persist($category);
    }

    public function remove(CategoryInterface $category): void
    {
        $this->entityManager->remove($category);
    }

    public function findById(string $id): ?CategoryInterface
    {
        /** @var CategoryInterface|null $category */
        $category = $this->repository->find($id);

        return $category;
    }

    /**
     * @return CategoryInterface[]
     */
    public function all(): array
    {
        /** @var CategoryInterface[] $categories */
        $categories = $this->repository->findBy([], ['name' => 'ASC']);

        return $categories;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
