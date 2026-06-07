<?php

declare(strict_types=1);

namespace App\Tests\Content\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class EditorialDoctrineTestCase extends KernelTestCase
{
    private static bool $schemaCreated = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->createSchemaIfNeeded();
    }

    protected function entityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        return $entityManager;
    }

    private function createSchemaIfNeeded(): void
    {
        if (self::$schemaCreated) {
            return;
        }

        $entityManager = $this->entityManager();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        self::$schemaCreated = true;
    }
}
