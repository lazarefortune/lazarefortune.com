<?php

declare(strict_types=1);

namespace App\Tests\Content\Service;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Content\Enum\ContentVisibility;
use App\Content\Enum\PublicationStatus;
use App\Content\Service\PublicationGuard;
use App\Playlist\Entity\Playlist;
use App\Video\Entity\Video;
use PHPUnit\Framework\TestCase;

final class PublicationGuardTest extends TestCase
{
    private PublicationGuard $guard;

    protected function setUp(): void
    {
        $this->guard = new PublicationGuard();
    }

    public function testDraftContentIsNotViewable(): void
    {
        $content = $this->createVideo(PublicationStatus::DRAFT, ContentVisibility::PUBLIC);

        $this->assertFalse($this->guard->canViewContent($content));
        $this->assertFalse($this->guard->canViewContent($content, new User()));
    }

    public function testScheduledContentIsNotViewableEvenWhenScheduledAtIsPast(): void
    {
        $content = $this->createVideo(PublicationStatus::SCHEDULED, ContentVisibility::PUBLIC);
        $content->setScheduledAt(new \DateTimeImmutable('-1 day'));

        $this->assertFalse($this->guard->canViewContent($content));
        $this->assertFalse($this->guard->canViewContent($content, new User()));
        $this->assertFalse($this->guard->isPubliclyVisible($content));
    }

    public function testArchivedContentIsNotViewable(): void
    {
        $content = $this->createArticle(PublicationStatus::ARCHIVED, ContentVisibility::PUBLIC);

        $this->assertFalse($this->guard->canViewContent($content, new User()));
    }

    public function testPublishedPublicContentIsVisibleToAnonymousUsers(): void
    {
        $content = $this->createVideo(PublicationStatus::PUBLISHED, ContentVisibility::PUBLIC);
        $content->setPublishedAt(new \DateTimeImmutable());

        $this->assertTrue($this->guard->canViewContent($content));
        $this->assertTrue($this->guard->isPubliclyVisible($content));
    }

    public function testPublishedMembersOnlyContentRequiresAuthenticatedUser(): void
    {
        $content = $this->createArticle(PublicationStatus::PUBLISHED, ContentVisibility::MEMBERS_ONLY);
        $content->setPublishedAt(new \DateTimeImmutable());

        $this->assertFalse($this->guard->canViewContent($content));
        $this->assertFalse($this->guard->isPubliclyVisible($content));
        $this->assertTrue($this->guard->canViewContent($content, new User()));
    }

    public function testPlaylistUsesSameVisibilityRules(): void
    {
        $playlist = new Playlist(new User());
        $playlist
            ->setTitle('Playlist')
            ->setSlug('playlist')
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setVisibility(ContentVisibility::MEMBERS_ONLY);

        $this->assertFalse($this->guard->canViewPlaylist($playlist));
        $this->assertTrue($this->guard->canViewPlaylist($playlist, new User()));
    }

    private function createVideo(PublicationStatus $status, ContentVisibility $visibility): Video
    {
        return (new Video(new User()))
            ->setTitle('Video')
            ->setSlug('video')
            ->setStatus($status)
            ->setVisibility($visibility);
    }

    private function createArticle(PublicationStatus $status, ContentVisibility $visibility): Article
    {
        return (new Article(new User()))
            ->setTitle('Article')
            ->setSlug('article')
            ->setStatus($status)
            ->setVisibility($visibility);
    }
}
