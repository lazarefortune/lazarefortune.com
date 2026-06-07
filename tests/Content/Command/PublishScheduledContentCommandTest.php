<?php

declare(strict_types=1);

namespace App\Tests\Content\Command;

use App\Auth\Entity\User;
use App\Content\Enum\PublicationStatus;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class PublishScheduledContentCommandTest extends EditorialDoctrineTestCase
{
    public function testDryRunDoesNotModifyDatabase(): void
    {
        $entityManager = $this->entityManager();
        $author = (new User())
            ->setEmail('command-dry-run@example.com')
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        $video = (new Video($author))
            ->setTitle('Command dry run')
            ->setSlug('command-dry-run')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-1 hour'));
        $entityManager->persist($video);
        $entityManager->flush();
        $entityManager->clear();

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--dry-run' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Would publish 1 content(s)', $tester->getDisplay());

        $reloaded = $entityManager->find(Video::class, $video->getId());
        $this->assertSame(PublicationStatus::SCHEDULED, $reloaded?->getStatus());
    }

    public function testCommandPublishesScheduledContent(): void
    {
        $entityManager = $this->entityManager();
        $author = (new User())
            ->setEmail('command-publish@example.com')
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        $video = (new Video($author))
            ->setTitle('Command publish')
            ->setSlug('command-publish')
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-15 minutes'));
        $entityManager->persist($video);
        $entityManager->flush();
        $entityManager->clear();

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Published', $tester->getDisplay());

        $reloaded = $entityManager->find(Video::class, $video->getId());
        $this->assertSame(PublicationStatus::PUBLISHED, $reloaded?->getStatus());
        $this->assertNotNull($reloaded?->getPublishedAt());
        $this->assertNull($reloaded?->getScheduledAt());
    }

    private function createCommandTester(): CommandTester
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('app:content:publish-scheduled');

        return new CommandTester($command);
    }
}
