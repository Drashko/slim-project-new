<?php

declare(strict_types=1);

use App\Integration\Doctrine\Fixtures\PermissionFixture;
use App\Integration\Doctrine\Fixtures\RoleFixture;
use App\Integration\Doctrine\Fixtures\UserFixture;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get(EntityManagerInterface::class);

$fixtures = [
    new PermissionFixture(),
    new RoleFixture(),
    new UserFixture(),
];

foreach ($fixtures as $fixture) {
    $fixture->load($entityManager);
}

echo "Database fixtures loaded for permissions, roles, and users." . PHP_EOL;
