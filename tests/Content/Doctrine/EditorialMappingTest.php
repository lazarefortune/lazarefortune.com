<?php

declare(strict_types=1);

namespace App\Tests\Content\Doctrine;

use App\Auth\Entity\User;
use App\Content\Entity\Article;
use App\Content\Enum\ContentType;
use App\Content\Enum\PublicationStatus;
use App\Video\Entity\Video;
use App\Video\Enum\VideoProvider;
use App\Video\Entity\VideoSource;

final class EditorialMappingTest extends EditorialDoctrineTestCase
{
    public function testJoinedInheritancePersistsVideoAndArticle(): void
    {
        $entityManager = $this->entityManager();

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hashed-password');
        $entityManager->persist($author);

        $video = (new Video($author))
            ->setTitle('Symfony routing')
            ->setSlug('symfony-routing')
            ->setDescription('Learn routing basics')
            ->setStatus(PublicationStatus::DRAFT);
        $video->addSource(new VideoSource($video, VideoProvider::YOUTUBE));

        $article = (new Article($author))
            ->setTitle('Doctrine inheritance')
            ->setSlug('doctrine-inheritance')
            ->setBody('Joined inheritance in Doctrine')
            ->setStatus(PublicationStatus::DRAFT);

        $entityManager->persist($video);
        $entityManager->persist($article);
        $entityManager->flush();
        $entityManager->clear();

        $reloadedVideo = $entityManager->find(Video::class, $video->getId());
        $reloadedArticle = $entityManager->find(Article::class, $article->getId());

        $this->assertInstanceOf(Video::class, $reloadedVideo);
        $this->assertInstanceOf(Article::class, $reloadedArticle);
        $this->assertSame(ContentType::VIDEO, $reloadedVideo?->getType());
        $this->assertSame(ContentType::ARTICLE, $reloadedArticle?->getType());
        $this->assertCount(1, $reloadedVideo?->getSources() ?? []);
    }
}
