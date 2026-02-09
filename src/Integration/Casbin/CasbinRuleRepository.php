<?php

declare(strict_types=1);

namespace App\Integration\Casbin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class CasbinRuleRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return CasbinRule[]
     */
    public function all(): array
    {
        return $this->entityManager->getRepository(CasbinRule::class)->findAll();
    }

    /**
     * @param array<int, string> $values
     */
    public function add(string $ptype, array $values): void
    {
        $rule = CasbinRule::fromPolicy($ptype, $values);
        $this->entityManager->persist($rule);
    }

    /**
     * @param array<int, string> $values
     */
    public function remove(string $ptype, array $values): void
    {
        $qb = $this->deleteBaseQuery();
        $qb->andWhere('r.ptype = :ptype')
            ->setParameter('ptype', $ptype);

        $this->applyValueConditions($qb, $values);
        $qb->getQuery()->execute();
    }

    /**
     * @param array<int, string> $fieldValues
     */
    public function removeFiltered(string $ptype, int $fieldIndex, array $fieldValues): void
    {
        $qb = $this->deleteBaseQuery();
        $qb->andWhere('r.ptype = :ptype')
            ->setParameter('ptype', $ptype);
        foreach ($fieldValues as $offset => $value) {
            if ($value === '') {
                continue;
            }

            $columnIndex = $fieldIndex + $offset;
            if ($columnIndex < 0 || $columnIndex > 5) {
                continue;
            }

            $column = 'v' . $columnIndex;
            $qb->andWhere(sprintf('r.%s = :%s', $column, $column))
                ->setParameter($column, $value);
        }

        $qb->getQuery()->execute();
    }

    public function clear(): void
    {
        $this->deleteBaseQuery()->getQuery()->execute();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    private function deleteBaseQuery(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->delete(CasbinRule::class, 'r');
    }

    /**
     * @param array<int, string> $values
     */
    private function applyValueConditions(QueryBuilder $qb, array $values): void
    {
        for ($index = 0; $index <= 5; $index += 1) {
            $column = 'v' . $index;
            $value = $values[$index] ?? null;
            if ($value === null || $value === '') {
                $qb->andWhere(sprintf('r.%s IS NULL', $column));
                continue;
            }

            $qb->andWhere(sprintf('r.%s = :%s', $column, $column))
                ->setParameter($column, $value);
        }
    }
}
