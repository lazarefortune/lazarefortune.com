<?php

namespace App\Domain\Notification\Subscriber;

use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Domain\Course\Repository\FormationRepository;
use App\Domain\Notification\NotificationService;
use App\Helper\TimeHelper;
use App\Infrastructure\Queue\EnqueueMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NotificationService $service,
        private readonly EnqueueMethod $enqueueMethod,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormationRepository $formationRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentUpdatedEvent::NAME => 'onUpdate',
            ContentCreatedEvent::NAME => 'onCreate',
            ContentDeletedEvent::NAME => 'onDelete',
        ];
    }

    /**
     * Quand un tutoriel/formation passe en ligne, on envoie une notification globale.
     */
    public function onUpdate(ContentUpdatedEvent $event): void
    {
        $content = $event->getContent();
        $previousContent = $event->getPrevious();
        if ($content instanceof Formation) {
            if ($previousContent->isOnline() !== $content->isOnline()) {
                /** @var Course $course */
                foreach ($content->getCourses() as $course) {
                    $course->setOnline($content->isOnline());
                }
                $this->entityManager->flush();
            }

            if ($content->isRestrictedToUser()) {
                /** @var Course $course */
                foreach ($content->getCourses() as $course) {
                    $course->setIsRestrictedToUser(true);
                }
                $this->entityManager->flush();
            }
        }

        if (
            ($content instanceof Course || $content instanceof Formation)
            && true === $content->isOnline()
            && false === $event->getPrevious()->isOnline()
            && $content->getCreatedAt() > new \DateTimeImmutable('- 1 days')
        ) {
            $this->notifyContent($content);
        }
    }

    /**
     * Quand un tutoriel/formation est créé en ligne, on envoie une notification globale.
     */
    public function onCreate(ContentCreatedEvent $event): void
    {
        $content = $event->getContent();

        if ($content instanceof Formation) {
            if ($content->isOnline()) {
                /** @var Course $course */
                foreach ($content->getCourses() as $course) {
                    $course->setOnline($content->isOnline());
                }
            }

            if ($content->isRestrictedToUser()) {
                /** @var Course $course */
                foreach ($content->getCourses() as $course) {
                    $course->setIsRestrictedToUser(true);
                }
            }

            $this->entityManager->flush();
        }

        if (
            ($content instanceof Course || $content instanceof Formation)
            && true === $content->isOnline()
        ) {
            $this->notifyContent($content);
        }
    }

    private function notifyContent(Course|Formation $content): void
    {
        $technologies = implode(', ', array_map(fn (Technology $t) => $t->getName(), $content->getMainTechnologies()));
        $duration = TimeHelper::duration($content->getDuration());

        if ($content instanceof Course) {
            $message = "Nouveau tutoriel {$technologies} !<br> <strong>{$content->getTitle()}</strong> <strong>({$duration})</strong>";
        } else {
            $message = "Nouvelle formation {$technologies} disponible :  <strong>{$content->getTitle()}</strong>";
        }

        // Le contenu est publié de suite
        if ($content->getCreatedAt() < new \DateTimeImmutable()) {
            $this->service->notifyChannel('public', $message, $content);
        } else {
            // On vide le contenu pour éviter de le stocker dans la sérialization
            $clonedContent = clone $content;
            $clonedContent->setContent(null);
            $this->enqueueMethod->enqueue(NotificationService::class, 'notifyChannel', [
                'public',
                $message,
                $clonedContent,
            ], $content->getCreatedAt());
        }
    }

    public function onDelete(ContentDeletedEvent $event): void
    {
        // TODO: Implement onDelete() method.

        $content = $event->getContent();

        if ($content instanceof Formation) {
            // delete playlist from Youtube

        }
    }
}