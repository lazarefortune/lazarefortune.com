<?php

declare(strict_types=1);

namespace App\Tests\Auth\Security;

use App\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    private static bool $schemaCreated = false;

    protected function createClientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $this->createSchemaIfNeeded();

        return $client;
    }

    protected function persistUser(string $email, array $roles): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser instanceof User) {
            return $existingUser;
        }

        $user = (new User())
            ->setEmail($email)
            ->setPassword('hashed-password')
            ->setRoles($roles);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createSchemaIfNeeded(): void
    {
        if (self::$schemaCreated) {
            return;
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
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
