<?php

namespace App\Domain\Auth\Registration\Service;

use App\Domain\Auth\Core\Dto\CreateUserDto;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Registration\Event\UserCreatedEvent;
use App\Domain\Auth\Registration\Event\UserValidatedEvent;
use App\Domain\Auth\Registration\Verifier\EmailVerifier;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly EventDispatcherInterface    $eventDispatcher,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository              $userRepository,
        private readonly VerifyEmailHelperInterface  $verifyEmailHelper,
        private readonly EmailVerifier               $emailVerifier,
        private readonly MailService                 $mailService,
        private readonly UrlGeneratorInterface       $urlGenerator
    )
    {
    }

    public function createUser( CreateUserDto $userDto ) : User
    {
        # check if the user already exists
        if ( $this->userRepository->findOneBy( [ 'email' => $userDto->email ] ) )
        {
            throw new Exception( "Un utilisateur avec cet email existe déjà." );
        }

        $user = $userDto->user
            ->setEmail( $userDto->email )
            ->setFullname( $userDto->fullname )
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $userDto->user,
                    $userDto->plainPassword
                )
            )
            ->setCgu( true );
        $this->entityManager->persist( $user );
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch( new UserCreatedEvent( $user ), UserCreatedEvent::NAME );

        return $user;
    }

    /**
     * Validate the email confirmation link, and activate the user account
     * @param mixed $userId
     * @param string $uri
     * @throws VerifyEmailExceptionInterface
     * @throws Exception
     */
    public function validateUser( mixed $userId, string $uri ) : void
    {
        if ( !$userId || !( $user = $this->userRepository->find( $userId ) ) )
        {
            throw new Exception( "L'utilisateur n'existe pas." );
        }

        if ( $user->isVerified() )
        {
            throw new Exception( "L'utilisateur a déjà été vérifié." );
        }

        $this->verifyEmailHelper->validateEmailConfirmation( $uri, $user->getId(), $user->getEmail() );

        $user->setIsVerified( true );
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch( new UserValidatedEvent( $user ), UserValidatedEvent::NAME );
    }

    /**
     * Send a welcome email to a new user
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendWelcomeEmail( User $user ) : void
    {
        $signatureComponents = $this->emailVerifier->generateSignature( $user );

        $data = [
            'user' => $user,
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Bienvenue sur ' . $_ENV['APP_NAME'],
            'mails/auth/welcome.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    /**
     * Email confirm the user's email address
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendVerificationSuccessEmail( User $user ) : void
    {

        $data = [
            'user' => $user,
            'loginUrl' => $this->urlGenerator->generate( 'app_login', [], UrlGeneratorInterface::ABSOLUTE_URL )
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Votre compte est activé',
            'mails/auth/confirm-success.twig',
            $data
        );

        $this->mailService->send( $email );
    }
}