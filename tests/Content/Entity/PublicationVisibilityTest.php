<?php

declare(strict_types=1);

namespace App\Tests\Content\Entity;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Content\Enum\ContentVisibility;
use App\Content\Enum\ContentType;
use App\Content\Enum\PublicationStatus;
use App\Playlist\Entity\Playlist;
use App\Video\Entity\Video;
use PHPUnit\Framework\TestCase;

final class PublicationVisibilityTest extends TestCase
{
    public function testScheduledContentIsNotPubliclyVisibleEvenWhenScheduledAtIsPast(): void
    {
        $content = $this->createVideo();
        $content
            ->setStatus(PublicationStatus::SCHEDULED)
            ->setScheduledAt(new \DateTimeImmutable('-1 day'))
            ->setVisibility(ContentVisibility::PUBLIC);

        $this->assertFalse($content->isPubliclyVisible(null));
        $this->assertFalse($content->isPubliclyVisible(new User()));
    }

    public function testPublishedPublicContentIsVisibleToEveryone(): void
    {
        $content = $this->createVideo();
        $content
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setVisibility(ContentVisibility::PUBLIC);

        $this->assertTrue($content->isPubliclyVisible(null));
        $this->assertTrue($content->isPubliclyVisible(new User()));
    }

    public function testPublishedMembersOnlyContentRequiresAuthenticatedViewer(): void
    {
        $content = $this->createArticle();
        $content
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setVisibility(ContentVisibility::MEMBERS_ONLY);

        $this->assertFalse($content->isPubliclyVisible(null));
        $this->assertTrue($content->isPubliclyVisible(new User()));
    }

    public function testDraftAndArchivedContentAreNeverPubliclyVisible(): void
    {
        $video = $this->createVideo();
        $video->setVisibility(ContentVisibility::PUBLIC);

        $video->setStatus(PublicationStatus::DRAFT);
        $this->assertFalse($video->isPubliclyVisible(new User()));

        $video->setStatus(PublicationStatus::ARCHIVED);
        $this->assertFalse($video->isPubliclyVisible(new User()));
    }

    public function testPlaylistUsesSamePublicationVisibilityRules(): void
    {
        $playlist = new Playlist(new User());
        $playlist
            ->setStatus(PublicationStatus::PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setVisibility(ContentVisibility::MEMBERS_ONLY);

        $this->assertFalse($playlist->isPubliclyVisible(null));
        $this->assertTrue($playlist->isPubliclyVisible(new User()));
    }

    public function testContentTypeIsDerivedFromConcreteClass(): void
    {
        $this->assertSame(ContentType::VIDEO, $this->createVideo()->getType());
        $this->assertSame(ContentType::ARTICLE, $this->createArticle()->getType());
    }

    private function createVideo(): Video
    {
        return (new Video(new User()))
            ->setTitle('Video title')
            ->setSlug('video-title');
    }

    private function createArticle(): Article
    {
        return (new Article(new User()))
            ->setTitle('Article title')
            ->setSlug('article-title');
    }
}
