<?php

namespace App\Domain\Notification\Subscriber;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Event\CommentCreatedEvent;
use App\Domain\Notification\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $parent = $event->getComment()->getParent();

        // Le commentaire n'est pas une réponse, on ignore l'évènement
        if (null === $parent) {
            return;
        }

        $comments = collect($parent->getReplies()->toArray())
            ->push($parent)
            ->filter(fn (Comment $c) => null !== $c->getAuthor() && $comment->getAuthor() !== $c->getAuthor())
            ->keyBy(function (Comment $c) {
                $author = $c->getAuthor();

                return $author?->getId() ?? '0';
            });

        $excerpt = htmlentities(substr($parent->getContent(), 0, 40));
        $author = htmlentities($comment->getUsername());


        foreach ($comments as $c) {
            $this->notificationService->notifyUser($c->getAuthor(), "<strong>{$author}</strong> a répondu au commentaire « {$excerpt} »", $parent);
        }
    }
}