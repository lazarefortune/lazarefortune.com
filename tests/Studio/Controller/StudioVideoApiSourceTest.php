<?php

declare(strict_types=1);

namespace App\Tests\Studio\Controller;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Tests\Auth\Security\AuthenticatedWebTestCase;
use App\Video\Entity\Video;
use App\Video\Entity\VideoSource;
use App\Video\Enum\VideoProvider;
use App\Video\Enum\VideoVisibility;
use App\Video\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoApiSourceTest extends AuthenticatedWebTestCase
{
    public function testAnonymousUserIsRedirectedFromSourceUpdate(): void
    {
        $client = static::createClient();
        $client->request(
            'PATCH',
            '/studio/api/videos/1/source',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['sourceRef' => 'dQw4w9WgXcQ', 'visibility' => 'unlisted'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseRedirects('/login');
    }

    public function testRoleUserCannotUpdateSource(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-source-admin@example.com', [User::ROLE_ADMIN]);
        $user = $this->persistUser('studio-api-source-user@example.com', []);
        $video = $this->persistDraftVideo($admin, 'Video api source user', 'video-api-source-user');

        $client->loginUser($user);
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $video->getId()),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => 'dummy-token',
            ],
            content: json_encode(['sourceRef' => 'dQw4w9WgXcQ', 'visibility' => 'unlisted'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testInvalidCsrfIsRejected(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-source-csrf@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video api source csrf', 'video-api-source-csrf');

        $client->loginUser($admin);
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $video->getId()),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => 'invalid-token',
            ],
            content: json_encode(['sourceRef' => 'dQw4w9WgXcQ', 'visibility' => 'unlisted'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSourceUpdateCreatesSourceOnDraftVideo(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-source-create@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video api source create', 'video-api-source-create');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $csrfToken = $this->fetchSourceCsrfToken($client, $videoId);
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $videoId),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'sourceRef' => 'https://youtu.be/srcCr8Vid01',
                'visibility' => 'unlisted',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertSame('youtube', $payload['source']['provider']);
        $this->assertSame('srcCr8Vid01', $payload['source']['externalId']);
        $this->assertSame('unlisted', $payload['source']['visibility']);
        $this->assertNotEmpty($payload['source']['url']);

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $reloaded = $videoRepository->find($videoId);
        $this->assertInstanceOf(Video::class, $reloaded);
        $primarySource = $reloaded->getPrimarySource();
        $this->assertInstanceOf(VideoSource::class, $primarySource);
        $this->assertSame('srcCr8Vid01', $primarySource->getExternalId());
    }

    public function testSourceUpdateUpdatesExistingSource(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-api-source-update@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video api source update', 'video-api-source-update');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $existingSource = (new VideoSource($video, VideoProvider::YOUTUBE))
            ->setExternalId('old12345678')
            ->setUrl('https://www.youtube.com/watch?v=old12345678')
            ->setVisibility(VideoVisibility::PRIVATE)
            ->setIsPrimary(true);
        $video->addSource($existingSource);
        $entityManager->flush();
        $existingSourceId = $existingSource->getId();
        $this->assertNotNull($existingSourceId);

        $client->loginUser($admin);
        $csrfToken = $this->fetchSourceCsrfToken($client, $videoId);
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $videoId),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'sourceRef' => 'new12345678',
                'visibility' => 'public',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('new12345678', $payload['source']['externalId']);
        $this->assertSame('public', $payload['source']['visibility']);

        /** @var VideoRepository $videoRepository */
        $videoRepository = static::getContainer()->get(VideoRepository::class);
        $primarySource = $videoRepository->find($videoId)?->getPrimarySource();
        $this->assertInstanceOf(VideoSource::class, $primarySource);
        $this->assertSame($existingSourceId, $primarySource->getId());
    }

    public function testInvalidSourceRefReturnsJsonError(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-api-source-invalid@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video api source invalid', 'video-api-source-invalid');
        $videoId = $video->getId();
        $this->assertNotNull($videoId);

        $client->loginUser($admin);
        $csrfToken = $this->fetchSourceCsrfToken($client, $videoId);
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $videoId),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'sourceRef' => 'not-a-valid-youtube-reference',
                'visibility' => 'private',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($payload['success']);
        $this->assertNotEmpty($payload['error']);
    }

    public function testDuplicateSourceOnAnotherVideoIsRejected(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-api-source-dup@example.com', [User::ROLE_ADMIN]);

        $videoA = $this->persistDraftVideo($admin, 'Video A dup', 'video-a-dup');
        $videoB = $this->persistDraftVideo($admin, 'Video B dup', 'video-b-dup');

        $source = (new VideoSource($videoA, VideoProvider::YOUTUBE))
            ->setExternalId('dup12345678')
            ->setUrl('https://www.youtube.com/watch?v=dup12345678')
            ->setVisibility(VideoVisibility::PRIVATE)
            ->setIsPrimary(true);
        $videoA->addSource($source);
        $entityManager->flush();

        $client->loginUser($admin);
        $csrfToken = $this->fetchSourceCsrfToken($client, $videoB->getId());
        $client->request(
            'PATCH',
            sprintf('/studio/api/videos/%d/source', $videoB->getId()),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-CSRF-TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'sourceRef' => 'dup12345678',
                'visibility' => 'private',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('deja utilisee', $payload['error']);
    }

    private function fetchSourceCsrfToken(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, int $videoId): string
    {
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $videoId));
        $config = json_decode($crawler->filter('#studio-video-source-config')->text(), true, 512, JSON_THROW_ON_ERROR);

        return $config['csrfToken'];
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
