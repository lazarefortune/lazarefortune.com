<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
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
        $this->assertGreaterThan(0, $crawler->filter('[data-testid="studio-video-publish-now"]')->count());
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-action-bar"]', 'Publier maintenant');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Programmer');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Remettre en brouillon');
        $this->assertSelectorTextContains('[data-testid="studio-video-publish-options"]', 'Archiver');
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
