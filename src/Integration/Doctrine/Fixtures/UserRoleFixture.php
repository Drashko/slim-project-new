<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use App\Domain\Shared\DomainException;
use App\Domain\User\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserRoleFixture implements FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void
    {
        $repository = $entityManager->getRepository(User::class);

        $roleMatrix = [
            'admin@example.com' => ['super_admin', 'admin'],
            'api@example.com' => ['admin'],
            'jane.doe@example.com' => ['user'],
            'ops@example.com' => ['super_admin'],
            'customer@example.com' => ['customer'],
        ];

        foreach ($roleMatrix as $email => $roles) {
            $user = $repository->findOneBy(['email' => strtolower($email)]);
            if (!$user instanceof User) {
                throw new DomainException(sprintf('Cannot seed user_role: user "%s" is missing.', $email));
            }

            $user->setRoles($roles);
        }

        $entityManager->flush();
    }
}
