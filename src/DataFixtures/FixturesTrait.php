<?php

declare(strict_types=1);

namespace App\DataFixtures;

trait FixturesTrait
{
    private function truncateTable($entityClass)
    {
        $connection = $this->entityManager->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
        $connection->executeStatement(sprintf('TRUNCATE TABLE %s;', $this->entityManager->getClassMetadata($entityClass)->getTableName()));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
    }
}