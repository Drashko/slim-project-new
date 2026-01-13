<?php

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\ORM\Tools\Console\ConsoleRunner;


require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Add DI container definitions
$containerBuilder->addDefinitions(__DIR__ . '/config/container.php');

// Create DI container instance
try {
    $container = $containerBuilder->build();
} catch (Exception $e) {
}

// Create Slim App instance
$entityManager = $container->get(EntityManagerInterface::class);
// replace with path to your own project bootstrap file
//require_once 'bootstrap.php';

$commands = [
    // If you want to add your own custom console commands,
    // you can do so here.
];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);