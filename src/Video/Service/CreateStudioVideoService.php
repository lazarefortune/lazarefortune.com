<?php

declare(strict_types=1);

namespace App\Video\Service;

use App\Auth\Entity\User;
use App\Video\Dto\CreateDraftVideoInput;
use App\Video\Dto\CreateStudioVideoApiInput;
use App\Video\Dto\CreateStudioVideoResult;
use App\Video\Dto\UpdateVideoSourceInput;
use App\Video\Enum\VideoProvider;

final class CreateStudioVideoService
{
    public function __construct(
        private readonly CreateDraftVideoService $createDraftVideoService,
        private readonly UpdateVideoSourceService $updateVideoSourceService,
        private readonly YoutubeVideoIdExtractor $youtubeVideoIdExtractor,
    ) {
    }

    public function create(User $author, CreateStudioVideoApiInput $input): CreateStudioVideoResult
    {
        return match ($input->getMode()) {
            CreateStudioVideoApiInput::MODE_IDEA => $this->createIdea($author, $input),
            CreateStudioVideoApiInput::MODE_YOUTUBE_EXISTING => $this->createFromYoutubeExisting($author, $input),
            default => throw new \InvalidArgumentException('Mode de creation invalide.'),
        };
    }

    private function createIdea(User $author, CreateStudioVideoApiInput $input): CreateStudioVideoResult
    {
        $title = trim($input->getTitle());
        if ($title === '') {
            throw new \InvalidArgumentException('Le titre est obligatoire pour une idee de video.');
        }

        $video = $this->createDraftVideoService->create($author, (new CreateDraftVideoInput())->setTitle($title));

        return new CreateStudioVideoResult($video, 'content');
    }

    private function createFromYoutubeExisting(User $author, CreateStudioVideoApiInput $input): CreateStudioVideoResult
    {
        $sourceRef = trim($input->getSourceRef());
        if ($sourceRef === '') {
            throw new \InvalidArgumentException('L\'URL ou l\'identifiant YouTube est obligatoire.');
        }

        $externalId = $this->youtubeVideoIdExtractor->extract($sourceRef);
        if ($externalId === null) {
            throw new \InvalidArgumentException('URL ou identifiant YouTube invalide.');
        }

        $title = trim($input->getTitle());
        if ($title === '') {
            $title = sprintf('Video YouTube %s', $externalId);
        }

        $video = $this->createDraftVideoService->create($author, (new CreateDraftVideoInput())->setTitle($title));

        $sourceInput = (new UpdateVideoSourceInput())
            ->setSourceRef($sourceRef)
            ->setProvider(VideoProvider::YOUTUBE)
            ->setVisibility($input->getVisibility());

        $this->updateVideoSourceService->update($video, $sourceInput);

        return new CreateStudioVideoResult($video, 'video');
    }
}
