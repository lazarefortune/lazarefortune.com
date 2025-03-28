<?php

namespace App\Domain\Comment\Service;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\AuthService;
use App\Domain\Comment\DTO\CreateCommentDTO;
use App\Domain\Comment\DTO\UpdateCommentDTO;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Event\CommentCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentService
{
    public function __construct(
        private readonly AuthService              $auth,
        private readonly EntityManagerInterface   $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly RequestStack             $requestStack,
    )
    {
    }

    /**
     * Crée un commentaire et l'enregistre en base.
     */
    public function create( CreateCommentDTO $data ) : Comment
    {
        /** @var Content $target */
        $target = $this->em->getRepository( Content::class )->find( $data->target );
        /** @var Comment|null $parent */
        $parent = $data->parent ? $this->em->getReference( Comment::class, $data->parent ) : null;
        $comment = ( new Comment() )
            ->setAuthor( $this->auth->getUserOrNull() )
            ->setUsername( $data->username )
            ->setCreatedAt( new \DateTimeImmutable() )
            ->setContent( $data->content )
            ->setParent( $parent )
            ->setIp( $this->requestStack->getMainRequest()?->getClientIp() )
            ->setTarget( $target );
        $this->em->persist( $comment );
        $this->em->flush();
        $this->dispatcher->dispatch(new CommentCreatedEvent($comment));

        return $comment;
    }

    public function update( Comment $comment, UpdateCommentDTO $data ) : Comment
    {
        $comment->setContent( $data->content );
        $this->em->flush();

        return $comment;
    }

    public function delete( int $commentId ) : void
    {
        /** @var Comment $comment */
        $comment = $this->em->getReference( Comment::class, $commentId );
        $this->em->remove( $comment );
        $this->em->flush();
    }
}