<?php

namespace App\Domain\Badge\Subscriber;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Badge\BadgeService;
use App\Domain\Badge\Event\BadgeUnlockEvent;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Event\CommentCreatedEvent;
use App\Domain\Comment\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class BadgeUnlockSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BadgeService $service, private readonly EntityManagerInterface $em)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeUnlockEvent::class => 'onBadgeUnlock',
            CommentCreatedEvent::class => 'onCommentCreated',
            LoginSuccessEvent::class => 'onLogin',
        ];
    }

    public function onBadgeUnlock( BadgeUnlockEvent $event ) {
        $badge = $event->getBadge();
        $user = $event->getUser();

        // TODO: notify user
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
    }

}