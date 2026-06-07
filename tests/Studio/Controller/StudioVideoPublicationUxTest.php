<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Content\Enum\PublicationStatus;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoPublicationUxTest extends AuthenticatedWebTestCase
{
    public function testDraftEditPageShowsContextualPublicationActions(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-ux-draft@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistVideo($admin, 'Video UX draft', 'video-ux-draft', PublicationStatus::DRAFT);

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-publish-now"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Programmer');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Archiver');
        $this->assertSelectorNotExists('[data-testid="studio-video-draft-action"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-help"]', 'brouillon');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-help"]', 'n est pas visible');
        $this->assertSelectorExists('[data-testid="studio-video-schedule-section"]');
    }

    public function testScheduledEditPageShowsContextualPublicationActions(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-ux-scheduled@example.com', [User::ROLE_ADMIN]);
        $scheduledAt = new \DateTimeImmutable('+3 days');
        $video = $this->persistVideo(
            $admin,
            'Video UX scheduled',
            'video-ux-scheduled',
            PublicationStatus::SCHEDULED,
            scheduledAt: $scheduledAt,
        );

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-publish-now"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Modifier la programmation');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Remettre en brouillon');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Archiver');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-help"]', 'prevue');
        $this->assertSelectorTextContains('[data-testid="studio-video-scheduled-at"]', $scheduledAt->format('d/m/Y H:i'));
        $this->assertSelectorTextContains('[data-testid="studio-video-schedule-section"]', 'Modifier la programmation');
    }

    public function testPublishedEditPageShowsContextualPublicationActions(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-ux-published@example.com', [User::ROLE_ADMIN]);
        $publishedAt = new \DateTimeImmutable('-2 days');
        $video = $this->persistVideo(
            $admin,
            'Video UX published',
            'video-ux-published',
            PublicationStatus::PUBLISHED,
            publishedAt: $publishedAt,
        );

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-update-content"]');
        $this->assertSelectorNotExists('[data-testid="studio-video-publish-split"] [data-testid="studio-video-publish-now"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Remettre en brouillon');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Archiver');
        $this->assertSelectorNotExists('[data-testid="studio-video-schedule-link"]');
        $this->assertSelectorNotExists('[data-testid="studio-video-schedule-section"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-help"]', 'Publiee');
        $this->assertSelectorTextContains('[data-testid="studio-video-published-at"]', $publishedAt->format('d/m/Y H:i'));
    }

    public function testArchivedEditPageShowsContextualPublicationActions(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-ux-archived@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistVideo($admin, 'Video UX archived', 'video-ux-archived', PublicationStatus::ARCHIVED);

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-restore-draft"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Publier maintenant');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Programmer');
        $this->assertSelectorNotExists('[data-testid="studio-video-archive-action"]');
        $this->assertSelectorExists('[data-testid="studio-video-publication-alert"]');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-alert"]', 'archivee');
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-help"]', 'archivee');
    }

    public function testExistingPublicationPostRoutesStillWorkAfterUxChanges(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-ux-routes@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistVideo($admin, 'Video UX routes', 'video-ux-routes', PublicationStatus::DRAFT);
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());

        $this->assertResponseRedirects(sprintf('/studio/videos/%d/edit#publication', $videoId));
    }

    private function persistVideo(
        User $author,
        string $title,
        string $slug,
        PublicationStatus $status,
        ?\DateTimeImmutable $publishedAt = null,
        ?\DateTimeImmutable $scheduledAt = null,
    ): Video {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $video = (new Video($author))
            ->setTitle($title)
            ->setSlug($slug)
            ->setLevel(ContentLevel::BEGINNER)
            ->setStatus($status)
            ->setPublishedAt($publishedAt)
            ->setScheduledAt($scheduledAt);

        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
