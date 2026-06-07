<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Content\Enum\ContentVisibility;
use App\Content\Enum\PublicationStatus;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use App\Video\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoCreateTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromStudioVideoNew(): void
    {
        $client = static::createClient();
        $client->request('GET', '/studio/videos/new');

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotAccessStudioVideoNew(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('studio-video-new-user@example.com', []);

        $client->loginUser($user);
        $client->request('GET', '/studio/videos/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRoleAdminCanAccessStudioVideoNew(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-new-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouvelle vidéo');
        $this->assertSelectorTextContains('[data-studio-breadcrumb]', 'Nouvelle vidéo');
    }

    public function testValidPostCreatesDraftVideoAndRedirectsToIndex(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-create-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos/new');

        $form = $crawler->selectButton('Créer le brouillon')->form([
            'create_draft_video[title]' => 'Ma premiere video pedagogique',
            'create_draft_video[slug]' => 'ma-premiere-video-pedagogique',
            'create_draft_video[excerpt]' => 'Un extrait court pour la liste.',
            'create_draft_video[level]' => ContentLevel::BEGINNER->value,
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/studio/videos');
        $client->followRedirect();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="floating"] [data-flash-item].ds-alert-success');

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['slug' => 'ma-premiere-video-pedagogique']);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertSame('Ma premiere video pedagogique', $video->getTitle());
        $this->assertSame(PublicationStatus::DRAFT, $video->getStatus());
        $this->assertSame(ContentVisibility::PUBLIC, $video->getVisibility());
        $this->assertSame(ContentLevel::BEGINNER, $video->getLevel());
        $this->assertSame('Un extrait court pour la liste.', $video->getExcerpt());
        $this->assertSame($admin->getId(), $video->getAuthor()->getId());
        $this->assertNull($video->getPublishedAt());
        $this->assertNull($video->getScheduledAt());
        $this->assertCount(0, $video->getSources());
    }

    public function testSlugIsGeneratedFromTitleWhenEmpty(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-slug-auto@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos/new');

        $form = $crawler->selectButton('Créer le brouillon')->form([
            'create_draft_video[title]' => 'Hello Symfony World',
            'create_draft_video[slug]' => '',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/studio/videos');

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['slug' => 'hello-symfony-world']);

        $this->assertInstanceOf(Video::class, $video);
    }

    public function testDuplicateSlugGetsNumericSuffix(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-video-slug-dup@example.com', [User::ROLE_ADMIN]);

        $existingVideo = (new Video($admin))
            ->setTitle('Video existante')
            ->setSlug('slug-en-doublon');
        $entityManager->persist($existingVideo);
        $entityManager->flush();

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos/new');

        $form = $crawler->selectButton('Créer le brouillon')->form([
            'create_draft_video[title]' => 'Autre video',
            'create_draft_video[slug]' => 'slug-en-doublon',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/studio/videos');

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['slug' => 'slug-en-doublon-2']);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertSame('Autre video', $video->getTitle());
    }

    public function testCreatedVideoAppearsOnStudioVideosIndex(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-index-visible@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos/new');

        $form = $crawler->selectButton('Créer le brouillon')->form([
            'create_draft_video[title]' => 'Video visible dans la liste',
            'create_draft_video[slug]' => 'video-visible-dans-la-liste',
        ]);
        $client->submit($form);
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[data-studio-index-list]', 'Video visible dans la liste');
    }
}
