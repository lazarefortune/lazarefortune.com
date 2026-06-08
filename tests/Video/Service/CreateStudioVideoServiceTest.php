<?php

declare(strict_types=1);

namespace App\Tests\Video\Service;

use App\Auth\Entity\User;
use App\Content\Repository\ContentRepository;
use App\Video\Dto\CreateStudioVideoApiInput;
use App\Video\Enum\VideoVisibility;
use App\Video\Repository\VideoSourceRepository;
use App\Video\Service\CreateDraftVideoService;
use App\Video\Service\CreateStudioVideoService;
use App\Video\Service\UpdateVideoSourceService;
use App\Video\Service\YoutubeVideoIdExtractor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class CreateStudioVideoServiceTest extends TestCase
{
    public function testIdeaModeCreatesDraftWithoutSource(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $contentRepository = $this->createMock(ContentRepository::class);
        $videoSourceRepository = $this->createMock(VideoSourceRepository::class);

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $contentRepository->method('findOneBySlug')->willReturn(null);
        $videoSourceRepository->method('findOneBy')->willReturn(null);

        $service = new CreateStudioVideoService(
            new CreateDraftVideoService($entityManager, $contentRepository, new AsciiSlugger()),
            new UpdateVideoSourceService($entityManager, $videoSourceRepository, new YoutubeVideoIdExtractor()),
            new YoutubeVideoIdExtractor(),
        );

        $author = (new User())->setEmail('author@example.com')->setPassword('hash');
        $input = (new CreateStudioVideoApiInput())
            ->setMode(CreateStudioVideoApiInput::MODE_IDEA)
            ->setTitle('Mon idee');

        $result = $service->create($author, $input);

        $this->assertSame('content', $result->getRedirectFragment());
        $this->assertSame('Mon idee', $result->getVideo()->getTitle());
        $this->assertCount(0, $result->getVideo()->getSources());
    }

    public function testYoutubeExistingModeCreatesDraftWithSource(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $contentRepository = $this->createMock(ContentRepository::class);
        $videoSourceRepository = $this->createMock(VideoSourceRepository::class);

        $entityManager->expects($this->atLeastOnce())->method('persist');
        $entityManager->expects($this->atLeastOnce())->method('flush');
        $contentRepository->method('findOneBySlug')->willReturn(null);
        $videoSourceRepository->method('findOneBy')->willReturn(null);

        $service = new CreateStudioVideoService(
            new CreateDraftVideoService($entityManager, $contentRepository, new AsciiSlugger()),
            new UpdateVideoSourceService($entityManager, $videoSourceRepository, new YoutubeVideoIdExtractor()),
            new YoutubeVideoIdExtractor(),
        );

        $author = (new User())->setEmail('author@example.com')->setPassword('hash');
        $input = (new CreateStudioVideoApiInput())
            ->setMode(CreateStudioVideoApiInput::MODE_YOUTUBE_EXISTING)
            ->setTitle('Ma video')
            ->setSourceRef('dQw4w9WgXcQ')
            ->setVisibility(VideoVisibility::UNLISTED);

        $result = $service->create($author, $input);

        $this->assertSame('video', $result->getRedirectFragment());
        $primarySource = $result->getVideo()->getPrimarySource();
        $this->assertNotNull($primarySource);
        $this->assertSame('dQw4w9WgXcQ', $primarySource->getExternalId());
    }
}
