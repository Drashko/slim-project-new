<?php

declare(strict_types=1);

use App\Integration\Doctrine\Fixtures\CasbinRuleFixture;
use App\Integration\Doctrine\Fixtures\UserFixture;
use App\Integration\Doctrine\Fixtures\UserRoleFixture;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get(EntityManagerInterface::class);

$fixtures = [
    new UserFixture(),
    new UserRoleFixture(),
    new CasbinRuleFixture(),
];

foreach ($fixtures as $fixture) {
    $fixture->load($entityManager);
}

echo "Database fixtures loaded for users, user_role, and casbin_rule." . PHP_EOL;
