<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use App\Integration\Casbin\CasbinRule;
use Doctrine\ORM\EntityManagerInterface;

final class CasbinRuleFixture implements FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void
    {
        $entityManager->createQueryBuilder()
            ->delete(CasbinRule::class, 'r')
            ->getQuery()
            ->execute();

        $rules = [
            ['p', ['super_admin', '/api/v1/*', 'GET|POST|PUT|PATCH|DELETE', 'api']],
            ['p', ['admin', '/api/v1/users', 'GET|POST', 'api']],
            ['p', ['admin', '/api/v1/users/{id}', 'GET|PUT|PATCH', 'api']],
            ['p', ['customer', '/api/v1/users/{id}', 'GET', 'api']],
            ['p', ['user', '/api/v1/users/{id}', 'GET', 'api']],
            ['p', ['guest', '/api/v1/public*', 'GET', 'api']],
        ];

        foreach ($rules as [$ptype, $values]) {
            $entityManager->persist(CasbinRule::fromPolicy((string) $ptype, (array) $values));
        }

        $entityManager->flush();
    }
}
