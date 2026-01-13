<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\Role\Role;
use App\Domain\Role\RoleInterface;
use App\Domain\Role\RoleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class RoleRepository implements RoleRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Role::class);
    }

    public function add(RoleInterface $role): void
    {
        $this->entityManager->persist($role);
    }

    public function remove(RoleInterface $role): void
    {
        $this->entityManager->remove($role);
    }

    public function findByKey(string $key): ?RoleInterface
    {
        /** @var RoleInterface|null $role */
        $role = $this->repository->findOneBy(['key' => strtolower(trim($key))]);

        return $role;
    }

    /**
     * @return RoleInterface[]
     */
    public function all(): array
    {
        /** @var RoleInterface[] $roles */
        $roles = $this->repository->findBy([], ['name' => 'ASC']);

        return $roles;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
