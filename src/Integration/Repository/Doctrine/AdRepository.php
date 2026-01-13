<?php

declare(strict_types=1);

namespace App\Integration\Repository\Doctrine;

use App\Domain\Ad\Ad;
use App\Domain\Ad\AdInterface;
use App\Domain\Ad\AdRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class AdRepository implements AdRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Ad::class);
    }

    public function add(AdInterface $ad): void
    {
        $this->entityManager->persist($ad);
    }

    public function remove(AdInterface $ad): void
    {
        $this->entityManager->remove($ad);
    }

    public function find(string $id): ?AdInterface
    {
        /** @var AdInterface|null $ad */
        $ad = $this->repository->find($id);

        return $ad;
    }

    /**
     * @return AdInterface[]
     */
    public function findByUser(string $userId): array
    {
        $qb = $this->repository->createQueryBuilder('ad');
        $query = $qb
            ->where('ad.owner = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('ad.createdAt', 'DESC')
            ->getQuery();

        /** @var AdInterface[] $ads */
        $ads = $query->getResult();

        return $ads;
    }

    /**
     * @return AdInterface[]
     */
    public function findByFilters(
        ?string $category,
        ?string $status,
        ?string $userFilter,
        ?\DateTimeImmutable $fromDate,
        ?\DateTimeImmutable $toDate,
        ?string $ownerId = null
    ): array {
        $qb = $this->repository->createQueryBuilder('ad')
            ->join('ad.owner', 'owner');

        if ($ownerId !== null && $ownerId !== '') {
            $qb->andWhere('ad.owner = :ownerId')
                ->setParameter('ownerId', $ownerId);
        }

        if ($category !== null && $category !== '') {
            $qb->andWhere('ad.category = :category')
                ->setParameter('category', $category);
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('ad.status = :status')
                ->setParameter('status', $status);
        }

        if ($userFilter !== null && $userFilter !== '') {
            if (str_contains($userFilter, '@')) {
                $qb->andWhere('owner.email = :userFilter')
                    ->setParameter('userFilter', $userFilter);
            } else {
                $qb->andWhere('owner.id = :userFilter')
                    ->setParameter('userFilter', $userFilter);
            }
        }

        if ($fromDate instanceof \DateTimeImmutable) {
            $qb->andWhere('ad.createdAt >= :fromDate')
                ->setParameter('fromDate', $fromDate);
        }

        if ($toDate instanceof \DateTimeImmutable) {
            $qb->andWhere('ad.createdAt <= :toDate')
                ->setParameter('toDate', $toDate);
        }

        $query = $qb
            ->orderBy('ad.createdAt', 'DESC')
            ->getQuery();

        /** @var AdInterface[] $ads */
        $ads = $query->getResult();

        return $ads;
    }

    /**
     * @return AdInterface[]
     */
    public function all(): array
    {
        /** @var AdInterface[] $ads */
        $ads = $this->repository->findBy([], ['createdAt' => 'DESC']);

        return $ads;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
