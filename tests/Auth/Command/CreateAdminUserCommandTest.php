<?php

declare(strict_types=1);

namespace App\Tests\Auth\Command;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateAdminUserCommandTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->createSchema();
    }

    public function testCreatesNewAdminUser(): void
    {
        $commandTester = $this->createCommandTester();
        $exitCode = $commandTester->execute([
            'email' => 'new-admin@example.com',
            '--password' => 'secret1234',
            '--fullname' => 'New Admin',
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('created', $commandTester->getDisplay());

        $user = $this->userRepository->findOneByEmail('new-admin@example.com');
        $this->assertInstanceOf(User::class, $user);
        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
        $this->assertTrue($user->isVerified());
        $this->assertSame('New Admin', $user->getFullname());
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'secret1234'));
    }

    public function testPromotesExistingUserWithoutOverwritingPassword(): void
    {
        $user = (new User())
            ->setEmail('existing@example.com')
            ->setPassword($this->passwordHasher->hashPassword(new User(), 'oldpassword'))
            ->setRoles([]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $originalPassword = $user->getPassword();

        $commandTester = $this->createCommandTester();
        $exitCode = $commandTester->execute([
            'email' => 'existing@example.com',
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('promoted', $commandTester->getDisplay());

        $this->entityManager->clear();
        $promotedUser = $this->userRepository->findOneByEmail('existing@example.com');
        $this->assertInstanceOf(User::class, $promotedUser);
        $this->assertContains(User::ROLE_ADMIN, $promotedUser->getRoles());
        $this->assertSame($originalPassword, $promotedUser->getPassword());
        $this->assertTrue($this->passwordHasher->isPasswordValid($promotedUser, 'oldpassword'));
    }

    public function testSuperAdminOptionAddsSuperAdminRole(): void
    {
        $commandTester = $this->createCommandTester();
        $exitCode = $commandTester->execute([
            'email' => 'super@example.com',
            '--password' => 'secret1234',
            '--super-admin' => true,
        ], ['interactive' => false]);

        $this->assertSame(0, $exitCode);

        $user = $this->userRepository->findOneByEmail('super@example.com');
        $this->assertInstanceOf(User::class, $user);
        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
        $this->assertContains(User::ROLE_SUPER_ADMIN, $user->getRoles());
    }

    public function testInvalidEmailFails(): void
    {
        $commandTester = $this->createCommandTester();
        $exitCode = $commandTester->execute([
            'email' => 'not-an-email',
            '--password' => 'secret1234',
        ], ['interactive' => false]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Invalid email', $commandTester->getDisplay());
        $this->assertNull($this->userRepository->findOneByEmail('not-an-email'));
    }

    public function testNewUserRequiresPasswordInNonInteractiveMode(): void
    {
        $commandTester = $this->createCommandTester();
        $exitCode = $commandTester->execute([
            'email' => 'missing-password@example.com',
        ], ['interactive' => false]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('--password', $commandTester->getDisplay());
        $this->assertNull($this->userRepository->findOneByEmail('missing-password@example.com'));
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:user:create-admin');

        return new CommandTester($command);
    }

    private function createSchema(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
