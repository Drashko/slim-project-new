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

    public function persist(string $plainToken, string $userId, DateTimeImmutable $expiresAt, ?string $familyId = null): RefreshToken
    {
        $tokenHash = hash('sha256', $plainToken);
        $entity = new RefreshToken($tokenHash, $userId, $expiresAt, $familyId);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    public function find(string $plainToken): ?RefreshToken
    {
        $tokenHash = hash('sha256', $plainToken);
        /** @var RefreshToken|null $entity */
        $entity = $this->repository->findOneBy(['tokenHash' => $tokenHash]);

        return $entity;
    }

    public function revokeById(string $id, DateTimeImmutable $now, ?string $replacedBy = null): void
    {
        /** @var RefreshToken|null $entity */
        $entity = $this->repository->find($id);
        if ($entity === null) {
            return;
        }

        $entity->revoke($now, $replacedBy);
        $this->entityManager->flush();
    }

    public function revokeFamily(string $familyId, DateTimeImmutable $now): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $q = $qb
            ->update(RefreshToken::class, 'rt')
            ->set('rt.revokedAt', ':now')
            ->where('rt.familyId = :familyId')
            ->andWhere('rt.revokedAt IS NULL')
            ->setParameter('now', $now)
            ->setParameter('familyId', $familyId)
            ->getQuery();

        return (int) $q->execute();
    }

    public function purgeExpired(DateTimeImmutable $now): int
    {
        // purge expired & revoked tokens
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.expiresAt <= :now OR rt.revokedAt IS NOT NULL')
            ->setParameter('now', $now)
            ->getQuery();

        return (int) $query->execute();
    }
}
