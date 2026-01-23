<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\Token\RefreshToken;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(RefreshToken::class);
    }

    public function persist(string $token, string $userId, DateTimeImmutable $expiresAt): RefreshToken
    {
        $entity = new RefreshToken($token, $userId, $expiresAt);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    public function find(string $token): ?RefreshToken
    {
        /** @var RefreshToken|null $entity */
        $entity = $this->repository->find($token);

        return $entity;
    }

    public function revoke(string $token): void
    {
        $entity = $this->find($token);
        if ($entity !== null) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }

    public function purgeExpired(DateTimeImmutable $now): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.expiresAt <= :now')
            ->setParameter('now', $now)
            ->getQuery();

        return (int) $query->execute();
    }
}
