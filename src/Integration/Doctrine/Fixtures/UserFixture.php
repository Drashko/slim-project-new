<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use App\Domain\User\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserFixture implements FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void
    {
        $repository = $entityManager->getRepository(User::class);

        $users = [
            ['email' => 'admin@example.com', 'password' => 'admin123', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']],
            ['email' => 'api@example.com', 'password' => 'api123', 'roles' => ['ROLE_API']],
            ['email' => 'jane.doe@example.com', 'password' => 'user123', 'roles' => ['ROLE_USER']],
            ['email' => 'ops@example.com', 'password' => 'ops123', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']],
        ];

        foreach ($users as $userData) {
            $existing = $repository->findOneBy(['email' => strtolower($userData['email'])]);
            if ($existing instanceof User) {
                $existing->changePassword($userData['password']);
                $existing->setRoles($userData['roles']);
                $existing->setStatus('Active');
                continue;
            }

            $user = new User($userData['email'], $userData['password'], $userData['roles'], 'Active');
            $entityManager->persist($user);
        }

        $entityManager->flush();
    }
}
