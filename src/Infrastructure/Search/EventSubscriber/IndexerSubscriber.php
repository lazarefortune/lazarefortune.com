<?php

namespace App\Infrastructure\Search\EventSubscriber;

use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Formation;
use App\Infrastructure\Search\IndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IndexerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly IndexerInterface $indexer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentUpdatedEvent::NAME => 'updateContent',
            ContentCreatedEvent::NAME => 'indexContent',
            ContentDeletedEvent::NAME => 'removeContent',
        ];
    }

    public function indexContent(ContentCreatedEvent $event): void
    {

        $content = $event->getContent();
        /** @var array{id: string, title: string, content: string, created_at: int, category: string[]} $normalizedContent */
        $normalizedContent = $this->normalizer->normalize($content, 'search');

        if ($content->isOnline()) {
            $this->indexer->index($normalizedContent);

            // Si c'est une formation, indexer tous ses cours s'ils sont online
            if ($content instanceof Formation) {
                foreach ($content->getCourses() as $course) {
                    if ($course->isOnline()) {
                        $this->indexer->index($this->normalizer->normalize($course, 'search'));
                    }
                }
            }
        }
    }

    public function removeContent(ContentDeletedEvent $event): void
    {
        $content = $event->getContent();
        $this->indexer->remove((string) $content->getId());

        // Si c'est une formation, désindexer tous ses cours
        if ($content instanceof Formation) {
            foreach ($content->getCourses() as $course) {
                $this->indexer->remove((string) $course->getId());
            }
        }
    }

    public function updateContent(ContentUpdatedEvent $event): void
    {
        $previous = $event->getPrevious();
        $current = $event->getContent();

        /** @var array{id: string, title: string, content: string, created_at: int, category: string[]} $previousData */
        $previousData = $this->normalizer->normalize($previous, 'search');

        /** @var array{id: string, title: string, content: string, created_at: int, category: string[]} $currentData */
        $currentData = $this->normalizer->normalize($current, 'search');

        if ($current->isOnline() && (!$previous->isOnline() || $previousData !== $currentData)) {
            $this->indexer->index($currentData);

            // Si c'est une formation qui passe online, indexer tous ses cours
            if ($current instanceof Formation) {
                foreach ($current->getCourses() as $course) {
                    if ($course->isOnline()) {
                        $this->indexer->index($this->normalizer->normalize($course, 'search'));
                    }
                }
            }
        } elseif (!$current->isOnline() && $previous->isOnline()) {
            $this->indexer->remove((string) $current->getId());

            // Si une formation passe offline, désindexer tous ses cours
            if ($current instanceof Formation) {
                foreach ($current->getCourses() as $course) {
                    $this->indexer->remove((string) $course->getId());
                }
            }
        }
    }
}