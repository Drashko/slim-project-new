<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use App\Domain\Permission\Permission;
use App\Domain\Role\Role;
use Doctrine\ORM\EntityManagerInterface;

final class RoleFixture implements FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void
    {
        $roleRepository = $entityManager->getRepository(Role::class);
        $permissionRepository = $entityManager->getRepository(Permission::class);

        $roles = [
            'ROLE_USER' => [
                'name' => 'Standard user',
                'description' => 'Profile-level access for signed-in users.',
                'critical' => false,
                'members' => 128,
                'permissions' => ['profile.view'],
            ],
            'ROLE_API' => [
                'name' => 'API client',
                'description' => 'Machine-to-machine client for integrations.',
                'critical' => false,
                'members' => 6,
                'permissions' => ['api.access'],
            ],
            'ROLE_ADMIN' => [
                'name' => 'Administrator',
                'description' => 'Full administrative access to manage identities and permissions.',
                'critical' => true,
                'members' => 3,
                'permissions' => [
                    'admin.access',
                    'admin.users.manage',
                    'admin.roles.manage',
                    'admin.permissions.manage',
                    'admin.permissions.publish',
                    'admin.audit.view',
                    'profile.view',
                ],
            ],
        ];

        foreach ($roles as $key => $definition) {
            $role = $roleRepository->findOneBy(['key' => strtolower($key)]);

            if (!$role instanceof Role) {
                $role = new Role($key, $definition['name'], $definition['description'], $definition['critical'], $definition['members']);
                $entityManager->persist($role);
            } else {
                $role->setName($definition['name']);
                $role->setDescription($definition['description']);
                $role->markCritical($definition['critical']);
                $role->setMemberCount($definition['members']);
            }

            foreach ($definition['permissions'] as $permissionKey) {
                $permission = $permissionRepository->findOneBy(['key' => $permissionKey]);
                if ($permission instanceof Permission) {
                    $role->addPermission($permission);
                }
            }
        }

        $entityManager->flush();
    }
}
