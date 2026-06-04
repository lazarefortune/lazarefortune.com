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
            ContentCreatedEvent::NAME => 'indexContent',
            ContentUpdatedEvent::NAME => 'updateContent',
            ContentDeletedEvent::NAME => 'removeContent',
        ];
    }

    public function indexContent(ContentCreatedEvent $event): void
    {
        $this->index($event->getContent());
    }

    public function removeContent(ContentDeletedEvent $event): void
    {
        $content = $event->getContent();
        $this->indexer->remove((string) $content->getId());

        if ($content instanceof Formation) {
            foreach ($content->getCourses() as $course) {
                $this->indexer->remove((string) $course->getId());
            }
        }
    }

    public function updateContent(ContentUpdatedEvent $event): void
    {
        $this->index($event->getContent());
    }

    private function index(object $content): void
    {
        $this->indexer->index($this->normalizer->normalize($content, 'search'));

        if ($content instanceof Formation) {
            foreach ($content->getCourses() as $course) {
                $this->indexer->index($this->normalizer->normalize($course, 'search'));
            }
        }
    }
}
