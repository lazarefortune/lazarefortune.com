<?php

namespace App\Domain\Badge\Subscriber;

use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Badge\BadgeService;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Event\CommentCreatedEvent;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Course\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class BadgeUnlockSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BadgeService $service,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
            LoginSuccessEvent::class => 'onLogin',
            ContentCreatedEvent::NAME => 'onContentCreated',
            ContentUpdatedEvent::NAME => 'onContentUpdated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $author = $event->getComment()->getAuthor();
        if (!$author) {
            return;
        }
        /** @var CommentRepository $repository */
        $repository = $this->em->getRepository(Comment::class);
        $this->service->unlock($author, 'comments', $repository->count(['author' => $author]));
    }

    public function onLogin(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!($user instanceof User)) {
            return;
        }
        $this->service->unlock($user, 'years', (int) $user->getCreatedAt()->diff(new \DateTimeImmutable())->format('%y'));

        $daysCount = (int) $user->getCreatedAt()->diff(new \DateTimeImmutable())->format('%a');
        $this->service->unlock($user, 'days', $daysCount);
    }

    public function onContentCreated(ContentCreatedEvent $event): void
    {
        $author = $event->getContent()->getAuthor();
        if (!$author) {
            return;
        }

        if ($event->getContent() instanceof Course) {
            $count = $this->em->getRepository(Course::class)->count(['author' => $author, 'online' => true]);
            $this->service->unlock($author, 'published_videos', $count);
        }
    }

    public function onContentUpdated(ContentUpdatedEvent $event): void
    {
        $author = $event->getContent()->getAuthor();
        if (!$author) {
            return;
        }

        if ($event->getContent() instanceof Course) {
            $count = $this->em->getRepository(Course::class)->count(['author' => $author, 'online' => true]);
            $this->service->unlock($author, 'published_videos', $count);
        }
    }
}