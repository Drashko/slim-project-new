<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\User\UserRole;
use App\Domain\User\UserRoleInterface;
use App\Domain\User\UserRoleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class UserRoleRepository implements UserRoleRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(UserRole::class);
    }

    public function add(UserRoleInterface $userRole): void
    {
        $this->entityManager->persist($userRole);
    }

    public function remove(UserRoleInterface $userRole): void
    {
        $this->entityManager->remove($userRole);
    }

    public function findByUserIdAndRole(string $userId, string $role): ?UserRoleInterface
    {
        $normalizedRole = strtolower(trim($role));

        /** @var UserRoleInterface|null $item */
        $item = $this->repository->findOneBy([
            'user' => $userId,
            'role' => $normalizedRole,
        ]);

        return $item;
    }

    public function findByUserId(string $userId): array
    {
        /** @var UserRoleInterface[] $items */
        $items = $this->repository->findBy(['user' => $userId]);

        return $items;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
