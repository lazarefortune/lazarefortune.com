<?php

declare(strict_types=1);

namespace App\Content\Service;

use App\Auth\Entity\User;
use App\Content\Contract\PublishableResource;
use App\Content\Entity\Content;
use App\Content\Enum\ContentVisibility;
use App\Content\Enum\PublicationStatus;
use App\Playlist\Entity\Playlist;

final class PublicationGuard
{
    public function canViewContent(Content $content, ?User $user = null): bool
    {
        return self::evaluateVisibility($content->getStatus(), $content->getVisibility(), $user);
    }

    public function canViewPlaylist(Playlist $playlist, ?User $user = null): bool
    {
        return self::evaluateVisibility($playlist->getStatus(), $playlist->getVisibility(), $user);
    }

    public function isPubliclyVisible(PublishableResource $resource): bool
    {
        return self::evaluateVisibility($resource->getStatus(), $resource->getVisibility(), null);
    }

    /**
     * Single source of truth for publication visibility rules.
     */
    public static function evaluateVisibility(
        PublicationStatus $status,
        ContentVisibility $visibility,
        ?User $user,
    ): bool {
        if ($status !== PublicationStatus::PUBLISHED) {
            return false;
        }

        return match ($visibility) {
            ContentVisibility::PUBLIC => true,
            ContentVisibility::MEMBERS_ONLY => $user !== null,
        };
    }
}
