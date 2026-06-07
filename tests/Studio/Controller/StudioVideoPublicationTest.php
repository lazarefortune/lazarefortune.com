<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Content\Enum\PublicationStatus;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use App\Video\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoPublicationTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromPublishAction(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('anonymous-publish-admin@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video anonyme publish', 'video-anonyme-publish');

        $client->request('POST', sprintf('/studio/videos/%d/publish', $video->getId()), [
            '_token' => 'invalid',
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotPublishVideo(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('studio-video-publish-user@example.com', []);
        $admin = $this->persistUser('studio-video-publish-user-admin@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video interdite publish', 'video-interdite-publish');

        $client->loginUser($user);
        $client->request('POST', sprintf('/studio/videos/%d/publish', $video->getId()), [
            '_token' => 'invalid-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testInvalidCsrfTokenIsRejectedForPublish(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-publish-csrf@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video csrf publish', 'video-csrf-publish');

        $client->loginUser($admin);
        $client->request('POST', sprintf('/studio/videos/%d/publish', $video->getId()), [
            '_token' => 'invalid-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanPublishNow(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-publish-now@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video a publier', 'video-a-publier');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());

        $this->assertResponseRedirects(sprintf('/studio/videos/%d/edit#publication', $videoId));

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $publishedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $publishedVideo);
        $this->assertSame(PublicationStatus::PUBLISHED, $publishedVideo->getStatus());
        $this->assertNotNull($publishedVideo->getPublishedAt());
        $this->assertNull($publishedVideo->getScheduledAt());
    }

    public function testScheduleWithFutureDateSetsScheduledStatus(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-schedule-future@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video a programmer', 'video-a-programmer');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);
        $scheduledAt = (new \DateTimeImmutable('+2 days'))->format('Y-m-d\TH:i');

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-schedule-form"]')->form([
            'scheduled_at' => $scheduledAt,
        ]));

        $this->assertResponseRedirects(sprintf('/studio/videos/%d/edit#publication', $videoId));

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $scheduledVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $scheduledVideo);
        $this->assertSame(PublicationStatus::SCHEDULED, $scheduledVideo->getStatus());
        $this->assertNotNull($scheduledVideo->getScheduledAt());
        $this->assertNull($scheduledVideo->getPublishedAt());
    }

    public function testScheduleWithPastDateShowsErrorFlash(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-schedule-past@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video schedule past', 'video-schedule-past');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-schedule-form"]')->form([
            'scheduled_at' => (new \DateTimeImmutable('-1 hour'))->format('Y-m-d\TH:i'),
        ]));
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="floating"] [data-flash-item].ds-alert-danger');

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $unchangedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $unchangedVideo);
        $this->assertSame(PublicationStatus::DRAFT, $unchangedVideo->getStatus());
    }

    public function testDraftActionResetsPublicationState(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-draft-action@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video published to draft', 'video-published-to-draft');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());
        $crawler = $client->followRedirect();
        $client->submit($crawler->filter('[data-testid="studio-video-draft-form"]')->form());

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $draftVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $draftVideo);
        $this->assertSame(PublicationStatus::DRAFT, $draftVideo->getStatus());
        $this->assertNull($draftVideo->getPublishedAt());
        $this->assertNull($draftVideo->getScheduledAt());
    }

    public function testArchiveActionSetsArchivedStatus(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-archive-action@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video a archiver', 'video-a-archiver');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $publishedAt = $videoRepository->find($videoId)?->getPublishedAt();
        $this->assertNotNull($publishedAt);

        $crawler = $client->followRedirect();
        $client->submit($crawler->filter('[data-testid="studio-video-archive-form"]')->form());

        $archivedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $archivedVideo);
        $this->assertSame(PublicationStatus::ARCHIVED, $archivedVideo->getStatus());
        $this->assertNull($archivedVideo->getScheduledAt());
        $this->assertSame($publishedAt->getTimestamp(), $archivedVideo->getPublishedAt()?->getTimestamp());
    }

    public function testFlashSuccessAfterPublishAction(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-publish-flash@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video flash publish', 'video-flash-publish');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="floating"] [data-flash-item].ds-alert-success');
    }

    public function testEditPageShowsPublishedBadgeAfterPublish(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-badge@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video badge publish', 'video-badge-publish');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $client->submit($crawler->filter('[data-testid="studio-video-publish-form"]')->form());
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[data-testid="studio-video-publication-status"]', 'Publié');
        $this->assertSelectorExists('[data-testid="studio-video-update-content"]');
        $this->assertSelectorNotExists('[data-testid="studio-video-publish-split"] [data-testid="studio-video-publish-now"]');
        $this->assertSelectorExists('[data-testid="studio-video-draft-form"]');
        $this->assertSelectorExists('[data-testid="studio-video-archive-form"]');
    }

    private function persistDraftVideo(User $author, string $title, string $slug): Video
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $video = (new Video($author))
            ->setTitle($title)
            ->setSlug($slug)
            ->setLevel(ContentLevel::BEGINNER);

        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }
}
