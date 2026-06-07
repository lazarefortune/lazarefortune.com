<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Playlist\Entity\Playlist;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

final class StudioContentIndexTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromStudioVideos(): void
    {
        $client = static::createClient();
        $client->request('GET', '/studio/videos');

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotAccessStudioVideos(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('studio-videos-user@example.com', []);

        $client->loginUser($user);
        $client->request('GET', '/studio/videos');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRoleAdminCanAccessStudioVideos(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-videos-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-studio-breadcrumb]');
        $this->assertSelectorExists('[data-studio-menu-toggle]');
        $this->assertSelectorExists('[data-studio-mobile-drawer]');
    }

    public function testEmptyStudioVideosPageDisplaysEmptyState(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQueryBuilder()
            ->delete(Video::class, 'video')
            ->getQuery()
            ->execute();

        $admin = $this->persistUser('studio-videos-empty@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.ds-empty-state h2', 'Aucune vidéo');
    }

    public function testEmptyStudioArticlesPageDisplaysEmptyState(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQueryBuilder()
            ->delete(Article::class, 'article')
            ->getQuery()
            ->execute();

        $admin = $this->persistUser('studio-articles-empty@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/articles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.ds-empty-state h2', 'Aucun article');
    }

    public function testEmptyStudioPlaylistsPageDisplaysEmptyState(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQueryBuilder()
            ->delete(Playlist::class, 'playlist')
            ->getQuery()
            ->execute();

        $admin = $this->persistUser('studio-playlists-empty@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/playlists');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.ds-empty-state h2', 'Aucune playlist');
    }

    public function testStudioVideosPageWithContentRendersResponsiveIndexList(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-videos-list@example.com', [User::ROLE_ADMIN]);

        $video = (new Video($admin))
            ->setTitle('Video mobile first test')
            ->setSlug('video-mobile-first-test');
        $entityManager->persist($video);
        $entityManager->flush();

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[data-studio-breadcrumb]', 'Dashboard');
        $this->assertSelectorTextContains('[data-studio-breadcrumb]', 'Vidéos');
        $this->assertSelectorExists('[data-studio-index-list]');
        $this->assertSelectorExists('[data-studio-index-table]');
    }

    public function testRoleAdminCanAccessStudioArticles(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-articles-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/articles');

        $this->assertResponseIsSuccessful();
    }

    public function testRoleAdminCanAccessStudioPlaylists(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-playlists-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/playlists');

        $this->assertResponseIsSuccessful();
    }
}
