<?php

declare(strict_types=1);

namespace App\Integration\Doctrine\Fixtures;

use Doctrine\ORM\EntityManagerInterface;

interface FixtureInterface
{
    public function load(EntityManagerInterface $entityManager): void;
}
