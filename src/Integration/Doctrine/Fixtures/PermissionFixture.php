<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use App\Domain\Permission\Permission;
use Doctrine\ORM\EntityManagerInterface;

final class PermissionFixture implements FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void
    {
        $repository = $entityManager->getRepository(Permission::class);

        $permissions = [
            ['key' => 'auth.refresh', 'label' => 'Refresh tokens'],
            ['key' => 'profile.view', 'label' => 'View profile'],
            ['key' => 'api.access', 'label' => 'Access API endpoints'],
            ['key' => 'admin.access', 'label' => 'Access admin dashboard'],
            ['key' => 'admin.users.manage', 'label' => 'Manage users'],
            ['key' => 'admin.roles.manage', 'label' => 'Manage roles'],
            ['key' => 'admin.permissions.manage', 'label' => 'Manage permissions'],
            ['key' => 'admin.permissions.publish', 'label' => 'Publish permission updates'],
            ['key' => 'admin.audit.view', 'label' => 'View audit log'],
        ];

        foreach ($permissions as $permissionData) {
            $existing = $repository->findOneBy(['key' => $permissionData['key']]);
            if ($existing instanceof Permission) {
                continue;
            }

            $entityManager->persist(new Permission($permissionData['key'], $permissionData['label']));
        }

        $entityManager->flush();
    }
}
