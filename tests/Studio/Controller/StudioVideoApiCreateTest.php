<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use App\Video\Entity\VideoSource;
use App\Video\Enum\VideoProvider;
use App\Video\Enum\VideoVisibility;
use App\Video\Repository\VideoRepository;

final class StudioVideoApiCreateTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromApiCreate(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/studio/api/videos',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['mode' => 'idea', 'title' => 'Test'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotCreateVideoViaApi(): void
    {
        $client = $this->createClientWithSchema();
        $user = $this->persistUser('studio-api-video-user@example.com', []);

        $client->loginUser($user);
        $client->request(
            'POST',
            '/studio/api/videos',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => 'dummy-token',
            ],
            content: json_encode(['mode' => 'idea', 'title' => 'Test'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testInvalidCsrfIsRejected(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-video-csrf@example.com', [User::ROLE_ADMIN]);

        $client->loginUser($admin);
        $client->request(
            'POST',
            '/studio/api/videos',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => 'invalid-token',
            ],
            content: json_encode(['mode' => 'idea', 'title' => 'Test'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testIdeaModeCreatesDraftVideo(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-video-idea@example.com', [User::ROLE_ADMIN]);

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
                'title' => 'Mon idee de video',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertMatchesRegularExpression('~/studio/videos/\d+/edit#content$~', $payload['redirectUrl']);

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['title' => 'Mon idee de video']);
        $this->assertInstanceOf(Video::class, $video);
        $this->assertCount(0, $video->getSources());
    }

    public function testYoutubeExistingModeCreatesVideoAndSource(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-video-youtube@example.com', [User::ROLE_ADMIN]);

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
                'mode' => 'youtube_existing',
                'title' => 'Video YouTube importee',
                'sourceRef' => 'https://youtu.be/9bZkp7q19f0',
                'visibility' => 'unlisted',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertMatchesRegularExpression('~/studio/videos/\d+/edit#video$~', $payload['redirectUrl']);

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $video = $videoRepository->findOneBy(['title' => 'Video YouTube importee']);
        $this->assertInstanceOf(Video::class, $video);

        $primarySource = $video->getPrimarySource();
        $this->assertInstanceOf(VideoSource::class, $primarySource);
        $this->assertSame(VideoProvider::YOUTUBE, $primarySource->getProvider());
        $this->assertSame('9bZkp7q19f0', $primarySource->getExternalId());
        $this->assertSame(VideoVisibility::UNLISTED, $primarySource->getVisibility());
    }

    public function testYoutubeExistingRejectsInvalidSourceRef(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-video-invalid@example.com', [User::ROLE_ADMIN]);

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
                'mode' => 'youtube_existing',
                'title' => 'Video invalide',
                'sourceRef' => 'pas-une-url-youtube',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($payload['success']);
    }

    private function fetchCreateCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/studio/videos/new');
        $config = json_decode($crawler->filter('#studio-video-create-config')->text(), true, 512, JSON_THROW_ON_ERROR);

        return $config['csrfToken'];
    }
}
