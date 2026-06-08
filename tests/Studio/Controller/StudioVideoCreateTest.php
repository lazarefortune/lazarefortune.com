<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Repository\VideoRepository;

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

    public function testRoleAdminCanAccessStudioVideoNewWithReactMount(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-new-admin@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request('GET', '/studio/videos/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter une vidéo');
        $this->assertSelectorExists('[data-testid="studio-video-create-app"]');
        $this->assertSelectorExists('#studio-video-create-config');
        $this->assertSelectorExists('script[src*="/build/studio"]');
    }

    public function testCreatedVideoViaApiAppearsOnStudioVideosIndex(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-index-visible@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $csrfToken = $this->fetchCreateCsrfToken($client);
        $client->request(
            'POST',
            '/studio/api/videos',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'mode' => 'idea',
                'title' => 'Video visible dans la liste',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $client->request('GET', $payload['redirectUrl']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Video visible dans la liste');

        $client->request('GET', '/studio/videos');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('[data-studio-index-list]', 'Video visible dans la liste');
    }

    public function testApiIdeaCreatesUniqueSlugFromTitle(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-slug-auto@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $csrfToken = $this->fetchCreateCsrfToken($client);
        $client->request(
            'POST',
            '/studio/api/videos',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'mode' => 'idea',
                'title' => 'Hello Symfony World',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['slug' => 'hello-symfony-world']);

        $this->assertNotNull($video);
        $this->assertSame('Hello Symfony World', $video->getTitle());
    }

    private function fetchCreateCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/studio/videos/new');
        $config = json_decode($crawler->filter('#studio-video-create-config')->text(), true, 512, JSON_THROW_ON_ERROR);

        return $config['csrfToken'];
    }
}
