<?php

declare(strict_types=1);

namespace App\Tests\Video\Service;

use App\Auth\Entity\User;
use App\Content\Enum\ContentLevel;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Dto\UpdateVideoSourceInput;
use App\Video\Entity\Video;
use App\Video\Entity\VideoSource;
use App\Video\Enum\VideoProvider;
use App\Video\Enum\VideoVisibility;
use App\Video\Service\UpdateVideoSourceService;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateVideoSourceServiceTest extends EditorialDoctrineTestCase
{
    public function testCreatePrimarySourceFromDirectId(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);
        $youtubeId = $this->fakeYoutubeId();

        $source = $this->service()->update($video, (new UpdateVideoSourceInput())
            ->setSourceRef($youtubeId)
            ->setVisibility(VideoVisibility::UNLISTED));

        $this->assertTrue($source->isPrimary());
        $this->assertSame(VideoProvider::YOUTUBE, $source->getProvider());
        $this->assertSame($youtubeId, $source->getExternalId());
        $this->assertSame(VideoVisibility::UNLISTED, $source->getVisibility());
        $this->assertSame(sprintf('https://www.youtube.com/watch?v=%s', $youtubeId), $source->getUrl());
        $this->assertSame($source, $video->getPrimarySource());
    }

    public function testCreatePrimarySourceFromWatchUrl(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);
        $youtubeId = $this->fakeYoutubeId();

        $source = $this->service()->update($video, (new UpdateVideoSourceInput())
            ->setSourceRef(sprintf('https://www.youtube.com/watch?v=%s', $youtubeId))
            ->setVisibility(VideoVisibility::PUBLIC));

        $this->assertSame($youtubeId, $source->getExternalId());
        $this->assertSame(sprintf('https://www.youtube.com/watch?v=%s', $youtubeId), $source->getUrl());
    }

    public function testCreatePrimarySourceFromYoutuBeUrl(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);
        $youtubeId = $this->fakeYoutubeId();

        $source = $this->service()->update($video, (new UpdateVideoSourceInput())
            ->setSourceRef(sprintf('https://youtu.be/%s', $youtubeId))
            ->setVisibility(VideoVisibility::PRIVATE));

        $this->assertSame($youtubeId, $source->getExternalId());
        $this->assertSame(sprintf('https://youtu.be/%s', $youtubeId), $source->getUrl());
    }

    public function testUpdateExistingPrimarySource(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);
        $service = $this->service();

        $firstId = $this->fakeYoutubeId();
        $secondId = $this->fakeYoutubeId();

        $service->update($video, (new UpdateVideoSourceInput())
            ->setSourceRef($firstId)
            ->setVisibility(VideoVisibility::PRIVATE));

        $existingSourceId = $video->getPrimarySource()?->getId();
        $this->assertNotNull($existingSourceId);

        $updatedSource = $service->update($video, (new UpdateVideoSourceInput())
            ->setSourceRef(sprintf('https://youtu.be/%s', $secondId))
            ->setVisibility(VideoVisibility::PUBLIC)
            ->setThumbnailUrl('https://img.example/thumb.jpg')
            ->setDurationSeconds(360));

        $this->assertSame($existingSourceId, $updatedSource->getId());
        $this->assertSame($secondId, $updatedSource->getExternalId());
        $this->assertSame(VideoVisibility::PUBLIC, $updatedSource->getVisibility());
        $this->assertSame('https://img.example/thumb.jpg', $updatedSource->getThumbnailUrl());
        $this->assertSame(360, $updatedSource->getDurationSeconds());
        $this->assertCount(1, $video->getSources());
    }

    public function testEmptySourceRefIsRejected(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La reference source est obligatoire.');

        $this->service()->update($video, new UpdateVideoSourceInput());
    }

    public function testInvalidSourceRefIsRejected(): void
    {
        $entityManager = $this->entityManager();
        $video = $this->persistVideo($entityManager);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL ou identifiant YouTube invalide.');

        $this->service()->update($video, (new UpdateVideoSourceInput())->setSourceRef('invalid-source'));
    }

    public function testDuplicateExternalIdOnAnotherVideoIsRejected(): void
    {
        $entityManager = $this->entityManager();
        $author = $this->persistAuthor($entityManager);

        $firstVideo = $this->persistVideo($entityManager, $author, 'first-video');
        $secondVideo = $this->persistVideo($entityManager, $author, 'second-video');
        $service = $this->service();
        $sharedId = $this->fakeYoutubeId();

        $service->update($firstVideo, (new UpdateVideoSourceInput())
            ->setSourceRef($sharedId)
            ->setVisibility(VideoVisibility::PRIVATE));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('deja utilisee');

        $service->update($secondVideo, (new UpdateVideoSourceInput())
            ->setSourceRef($sharedId)
            ->setVisibility(VideoVisibility::PRIVATE));
    }

    private function service(): UpdateVideoSourceService
    {
        /** @var UpdateVideoSourceService $service */
        $service = static::getContainer()->get(UpdateVideoSourceService::class);

        return $service;
    }

    private function persistVideo(EntityManagerInterface $entityManager, ?User $author = null, ?string $slug = null): Video
    {
        $author ??= $this->persistAuthor($entityManager);
        $slug ??= 'video-source-test-'.uniqid('', true);

        $video = (new Video($author))
            ->setTitle('Video source test')
            ->setSlug($slug)
            ->setLevel(ContentLevel::BEGINNER);

        $entityManager->persist($video);
        $entityManager->flush();

        return $video;
    }

    private function persistAuthor(EntityManagerInterface $entityManager): User
    {
        $author = (new User())
            ->setEmail(sprintf('video-source-%s@example.com', uniqid('', true)))
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        return $author;
    }

    private function fakeYoutubeId(): string
    {
        return substr(str_pad(str_replace('.', '', uniqid('', true)), 11, '0'), 0, 11);
    }
}
