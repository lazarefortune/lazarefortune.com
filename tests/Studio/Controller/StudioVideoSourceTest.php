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
use Doctrine\ORM\EntityManagerInterface;

final class StudioVideoSourceTest extends AuthenticatedWebTestCase
{
    public function testSourceTabContainsReactMountAndConfig(): void
    {
        $client = $this->createClientWithSchema();
        $admin = $this->persistUser('studio-video-source-empty@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video sans source', 'video-sans-source');

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-testid="studio-video-source-app"]');
        $this->assertSelectorExists('#studio-video-source-config');
        $this->assertSelectorExists('script[src*="/build/studio"]');

        $config = json_decode($crawler->filter('#studio-video-source-config')->text(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($video->getId(), $config['videoId']);
        $this->assertNull($config['initialSource']);
        $this->assertNotEmpty($config['updateSourceUrl']);
        $this->assertNotEmpty($config['csrfToken']);
        $this->assertNotEmpty($config['availableProviders']);
    }

    public function testSourceTabConfigIncludesExistingSource(): void
    {
        $client = $this->createClientWithSchema();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->persistUser('studio-video-source-config@example.com', [User::ROLE_ADMIN]);
        $video = $this->persistDraftVideo($admin, 'Video avec source', 'video-avec-source-config');

        $source = (new VideoSource($video, VideoProvider::YOUTUBE))
            ->setExternalId('cfg12345678')
            ->setUrl('https://www.youtube.com/watch?v=cfg12345678')
            ->setVisibility(VideoVisibility::UNLISTED)
            ->setIsPrimary(true);
        $video->addSource($source);
        $entityManager->flush();

        $client->loginUser($admin);
        $crawler = $client->request('GET', sprintf('/studio/videos/%d/edit', $video->getId()));

        $config = json_decode($crawler->filter('#studio-video-source-config')->text(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('youtube', $config['initialSource']['provider']);
        $this->assertSame('cfg12345678', $config['initialSource']['externalId']);
        $this->assertSame('unlisted', $config['initialSource']['visibility']);
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
