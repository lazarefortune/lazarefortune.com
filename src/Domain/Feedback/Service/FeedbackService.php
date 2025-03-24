<?php

namespace App\Domain\Feedback\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Feedback\Entity\Feedback;
use App\Domain\Feedback\Enum\FeedbackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FeedbackService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface  $tokenStorage
    )
    {
    }

    public function prepareFeedback( FeedbackType $type ) : Feedback
    {
        $feedback = new Feedback();
        $feedback->setType( $type );

        $user = $this->tokenStorage->getToken()?->getUser();
        if ( $user instanceof User ) {
            $feedback->setUser( $user );
            $feedback->setFirstname( $user->getFullname() );
            $feedback->setEmail( $user->getEmail() );
        }

        return $feedback;
    }

    public function save( Feedback $feedback ) : void
    {
        $this->em->persist( $feedback );
        $this->em->flush();
    }
}
