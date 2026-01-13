<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\Permission\Permission;
use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class PermissionRepository implements PermissionRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Permission::class);
    }

    public function add(PermissionInterface $permission): void
    {
        $this->entityManager->persist($permission);
    }

    public function remove(PermissionInterface $permission): void
    {
        $this->entityManager->remove($permission);
    }

    public function findByKey(string $key): ?PermissionInterface
    {
        /** @var PermissionInterface|null $permission */
        $permission = $this->repository->findOneBy(['key' => strtolower(trim($key))]);

        return $permission;
    }

    /**
     * @return PermissionInterface[]
     */
    public function all(): array
    {
        /** @var PermissionInterface[] $permissions */
        $permissions = $this->repository->findBy([], ['key' => 'ASC']);

        return $permissions;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
