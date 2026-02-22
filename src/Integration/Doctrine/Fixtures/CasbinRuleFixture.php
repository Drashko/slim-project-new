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
            // NOTE: subjects must match CasbinAuthorizationMiddleware output: user:<id> and role:<name>
            ['p', ['role:super_admin', '/api/v1/*', 'GET|POST|PUT|PATCH|DELETE', 'api']],
            ['p', ['role:super_admin', '/_internal/api/v1/*', 'GET|POST|PUT|PATCH|DELETE', 'api']],

            // Admin can do anything on /api/v1/users* (covers /users and /users/{id})
            ['p', ['role:admin', '/api/v1/users*', 'GET|POST|PUT|PATCH|DELETE', 'api']],
            ['p', ['role:admin', '/_internal/api/v1/users*', 'GET|POST|PUT|PATCH|DELETE', 'api']],

            // Basic "who am I" endpoint for authenticated users
            ['p', ['role:admin', '/api/v1/me', 'GET', 'api']],
            ['p', ['role:user', '/api/v1/me', 'GET', 'api']],
            ['p', ['role:customer', '/api/v1/me', 'GET', 'api']],
            ['p', ['role:super_admin', '/api/v1/me', 'GET', 'api']],

            ['p', ['role:admin', '/_internal/api/v1/me', 'GET', 'api']],
            ['p', ['role:user', '/_internal/api/v1/me', 'GET', 'api']],
            ['p', ['role:customer', '/_internal/api/v1/me', 'GET', 'api']],
            ['p', ['role:super_admin', '/_internal/api/v1/me', 'GET', 'api']],

            // Examples: ordinary users can only read their own record (your handler should still enforce ownership)
            ['p', ['role:customer', '/api/v1/users/{id}', 'GET', 'api']],
            ['p', ['role:user', '/api/v1/users/{id}', 'GET', 'api']],

            // Ordinary users/customers may update only their own record (ownership enforced in endpoint)
            ['p', ['role:customer', '/api/v1/users/{id}', 'PUT|PATCH', 'api']],
            ['p', ['role:user', '/api/v1/users/{id}', 'PUT|PATCH', 'api']],

            ['p', ['role:customer', '/_internal/api/v1/users/{id}', 'GET', 'api']],
            ['p', ['role:user', '/_internal/api/v1/users/{id}', 'GET', 'api']],

            // Same for internal routes (BFF)
            ['p', ['role:customer', '/_internal/api/v1/users/{id}', 'PUT|PATCH', 'api']],
            ['p', ['role:user', '/_internal/api/v1/users/{id}', 'PUT|PATCH', 'api']],
        ];

        foreach ($rules as [$ptype, $values]) {
            $entityManager->persist(CasbinRule::fromPolicy((string) $ptype, (array) $values));
        }

        $entityManager->flush();
    }
}
