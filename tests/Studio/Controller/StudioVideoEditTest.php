<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use App\Video\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoEditTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromStudioVideoEdit(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('anonymous-edit-admin@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video anonyme edit', 'video-anonyme-edit');

        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotAccessStudioVideoEdit(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('studio-video-edit-user@example.com', []);
        $admin = $this->persistUser('studio-video-edit-user-admin@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video interdite au user', 'video-interdite-au-user');

        $client->loginUser($user);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRoleAdminCanAccessStudioVideoEdit(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-admin@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video de demonstration', 'video-de-demonstration-edit-admin');

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-edit-page"]');
        $this->assertSelectorTextContains('h1', 'Video de demonstration');
        $this->assertSelectorTextContains('[data-studio-breadcrumb]', 'Modifier');
    }

    public function testStudioVideoEditReturns404ForMissingVideo(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-404@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos/999999/edit');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testValidPostRedirectsToStudioVideoEdit(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-redirect@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos/new');

        $form = $crawler->selectButton('Créer le brouillon')->form([
            'create_draft_video[title]' => 'Video redirigee vers edit',
            'create_draft_video[slug]' => 'video-redirigee-vers-edit',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();
        $location = (string) $client->getResponse()->headers->get('Location');
        $this->assertMatchesRegularExpression('#/studio/videos/\d+/edit$#', $location);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Video redirigee vers edit');
        $this->assertSelectorExists('[data-testid="studio-video-edit-tabs"]');
    }

    public function testStudioVideosIndexDisplaysEditLink(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-index-link@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video avec lien modifier', 'video-avec-lien-modifier');

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/studio/videos');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('[data-studio-edit-link]')->count());
        $this->assertSelectorExists(sprintf('a[href="/studio/videos/%d/edit"]', $video->getId()));
    }

    public function testStudioVideoEditContainsFourTabs(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-tabs@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video onglets edit', 'video-onglets-edit');

        $client->loginUser($admin);
        $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-tab-content"]');
        $this->assertSelectorExists('[data-testid="studio-video-tab-source"]');
        $this->assertSelectorExists('[data-testid="studio-video-tab-classification"]');
        $this->assertSelectorExists('[data-testid="studio-video-tab-publication"]');
        $this->assertSelectorExists('[data-testid="studio-video-panel-content"]');
        $this->assertSelectorExists('[data-testid="studio-video-panel-source"]');
        $this->assertSelectorExists('[data-testid="studio-video-panel-classification"]');
        $this->assertSelectorExists('[data-testid="studio-video-panel-publication"]');
    }

    public function testAdminCanUpdateVideoContent(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-update@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Titre initial', 'titre-initial');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));

        $form = $crawler->selectButton('Enregistrer')->form([
            'update_video_content[title]' => 'Titre modifie',
            'update_video_content[slug]' => 'titre-modifie',
            'update_video_content[excerpt]' => 'Nouvel extrait.',
            'update_video_content[description]' => 'Description pedagogique complete.',
            'update_video_content[level]' => ContentLevel::INTERMEDIATE->value,
            'update_video_content[coverImagePath]' => 'covers/titre-modifie.jpg',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects(sprintf('/studio/videos/%d/edit#content', $videoId));
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Titre modifie');

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $updatedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $updatedVideo);
        $this->assertSame('Titre modifie', $updatedVideo->getTitle());
        $this->assertSame('titre-modifie', $updatedVideo->getSlug());
        $this->assertSame('Nouvel extrait.', $updatedVideo->getExcerpt());
        $this->assertSame('Description pedagogique complete.', $updatedVideo->getDescription());
        $this->assertSame(ContentLevel::INTERMEDIATE, $updatedVideo->getLevel());
        $this->assertSame('covers/titre-modifie.jpg', $updatedVideo->getCoverImagePath());
    }

    public function testSlugIsGeneratedFromTitleWhenEmptyOnUpdate(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-slug-auto@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Ancien titre', 'ancien-titre');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));

        $form = $crawler->selectButton('Enregistrer')->form([
            'update_video_content[title]' => 'Unique Edit Slug Auto Title',
            'update_video_content[slug]' => '',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $updatedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $updatedVideo);
        $this->assertSame('unique-edit-slug-auto-title', $updatedVideo->getSlug());
    }

    public function testDuplicateSlugGetsNumericSuffixOnUpdate(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-video-edit-slug-dup@example.com', [User::ROLE_ADMIN]);

        $existingVideo = (new Video($admin))
            ->setTitle('Video existante edit dup')
            ->setSlug('slug-en-doublon-edit-update');
        $entityManager->persist($existingVideo);

        $video = (new Video($admin))
            ->setTitle('Video a modifier edit dup')
            ->setSlug('video-a-modifier-edit-update');
        $entityManager->persist($video);
        $entityManager->flush();
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));

        $form = $crawler->selectButton('Enregistrer')->form([
            'update_video_content[title]' => 'Video a modifier edit dup',
            'update_video_content[slug]' => 'slug-en-doublon-edit-update',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $updatedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $updatedVideo);
        $this->assertSame('slug-en-doublon-edit-update-2', $updatedVideo->getSlug());
    }

    public function testUnchangedSlugStaysUnchangedOnUpdate(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-slug-unchanged@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Titre original', 'slug-stable');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));

        $form = $crawler->selectButton('Enregistrer')->form([
            'update_video_content[title]' => 'Titre mis a jour sans changer le slug',
            'update_video_content[slug]' => 'slug-stable',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $updatedVideo = $videoRepository->find($videoId);

        $this->assertInstanceOf(Video::class, $updatedVideo);
        $this->assertSame('slug-stable', $updatedVideo->getSlug());
        $this->assertSame('Titre mis a jour sans changer le slug', $updatedVideo->getTitle());
    }

    public function testFlashSuccessAfterContentSave(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-flash@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video flash save', 'video-flash-save');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));

        $form = $crawler->selectButton('Enregistrer')->form([
            'update_video_content[title]' => 'Video flash enregistree',
            'update_video_content[slug]' => 'video-flash-enregistree',
        ]);
        $client->submit($form);
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-flash-messages][data-flash-mode="floating"] [data-flash-item].ds-alert-success');
    }

    public function testStudioVideoEditContainsGlobalPublishActions(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-edit-publish-actions@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video actions publication', 'video-actions-publication');

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-publish-action-bar"]');
        $this->assertSelectorExists('[data-testid="studio-video-publish-now"]');
        $this->assertSelectorExists('[data-testid="studio-video-publish-options"]');
        $this->assertSelectorExists('[data-testid="studio-video-publish-split"]');
        $this->assertSelectorExists('[data-testid="studio-video-content-form"]');
        $this->assertSelectorExists('[data-testid="studio-video-publish-form"]');
        $this->assertSelectorNotExists('[data-testid="studio-video-publish-now"][disabled]');
        $this->assertGreaterThan(0, $crawler->filter('[data-testid="studio-video-publish-now"]')->count());
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-action-bar"]', 'Publier maintenant');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Programmer');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Archiver');
        $this->assertSelectorNotExists('[data-testid="studio-video-draft-action"]');
        $retourButtons = $crawler
            ->filter('[data-testid="studio-video-publish-action-bar"] .ds-btn')
            ->reduce(static function (\Symfony\Component\DomCrawler\Crawler $node): bool {
                return trim($node->text()) === 'Retour';
            });
        $this->assertSame(0, $retourButtons->count());
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
