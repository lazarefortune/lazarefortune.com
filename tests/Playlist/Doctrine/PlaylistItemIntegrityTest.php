<?php

declare(strict_types=1);

namespace App\Tests\Playlist\Doctrine;

use App\Auth\Entity\User;
use App\Content\Enum\PublicationStatus;
use App\Playlist\Entity\Playlist;
use App\Playlist\Entity\PlaylistChapter;
use App\Playlist\Entity\PlaylistItem;
use App\Tests\Content\Doctrine\EditorialDoctrineTestCase;
use App\Video\Entity\Video;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class PlaylistItemIntegrityTest extends EditorialDoctrineTestCase
{
    public function testSameContentCannotBeAddedTwiceInSameChapter(): void
    {
        $entityManager = $this->entityManager();

        $author = (new User())
            ->setEmail('playlist-author@example.com')
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        $video = (new Video($author))
            ->setTitle('Duplicate guard')
            ->setSlug('duplicate-guard')
            ->setStatus(PublicationStatus::DRAFT);
        $playlist = (new Playlist($author))
            ->setTitle('Learning path')
            ->setSlug('learning-path')
            ->setStatus(PublicationStatus::DRAFT);
        $chapter = new PlaylistChapter($playlist, 'Chapter 1', 0);
        $playlist->addChapter($chapter);

        $entityManager->persist($video);
        $entityManager->persist($playlist);
        $entityManager->persist($chapter);
        $entityManager->persist(new PlaylistItem($chapter, $video, 0));
        $entityManager->flush();

        $entityManager->persist(new PlaylistItem($chapter, $video, 1));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }
}
