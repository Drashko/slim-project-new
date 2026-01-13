<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\User\User;
use App\Domain\User\UserInterface;
use App\Domain\User\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class UserRepository implements UserRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(User::class);
    }

    public function add(UserInterface $user): void
    {
        $this->entityManager->persist($user);
    }

    public function remove(UserInterface $user): void
    {
        $this->entityManager->remove($user);
    }

    public function find(string $id): ?UserInterface
    {
        /** @var UserInterface|null $user */
        $user = $this->repository->find($id);

        return $user;
    }

    public function findByEmail(string $email): ?UserInterface
    {
        /** @var UserInterface|null $user */
        $user = $this->repository->findOneBy(['email' => strtolower($email)]);

        return $user;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @return UserInterface[]
     */
    public function all(): array
    {
        /** @var UserInterface[] $users */
        $users = $this->repository->findAll();

        return $users;
    }
}
