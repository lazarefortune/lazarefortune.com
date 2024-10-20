<?php

namespace App\Domain\Auth\Login\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class LoginService
{
    public function __construct(
        private readonly UserAuthenticatorInterface $userAuthenticator,
        private readonly AppAuthenticator           $authenticator,
        private readonly EntityManagerInterface     $em,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function authenticateUser( User $user, Request $request ) : Response
    {
        $authenticationResponse = $this->userAuthenticator->authenticateUser(
            $user,
            $this->authenticator,
            $request
        );

        if ( !$authenticationResponse instanceof Response ) {
            throw new Exception( "L'authentification de l'utilisateur a échoué." );
        }

        return $authenticationResponse;
    }

    public function logout( Request $request = null ) : void
    {
        $request = $request ?: new Request();
        $request->getSession()->invalidate();
    }

    public function setLastLogin( User $user ) : void
    {
        $user->setLastLogin( new \DateTime() );
        $this->em->persist( $user );
        $this->em->flush();
    }
}