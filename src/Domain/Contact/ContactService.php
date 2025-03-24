<?php

namespace App\Domain\Contact;

use App\Domain\Contact\Dto\ContactData;
use App\Domain\Contact\Entity\Contact;
use App\Domain\Contact\Repository\ContactRepository;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ContactService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContactRepository $contactRepository,
        private readonly MailService       $mailService,
        private readonly string            $adminEmail
    )
    {
    }

    /**
     * Send contact message
     * @param ContactData $contactDto
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function sendContactMessage( ContactData $contactDto ) : void
    {
        if ( !$this->adminEmail ) {
            throw new Exception( 'Email invalide' );
        }

        // Admin email
        $contactEmail = $this->mailService->prepareEmail(
            $this->adminEmail,
            'Demande de contact: ' . $contactDto->subject,
            'mails/admin/contact/message-received.twig', [
            'name' => $contactDto->name,
            'message' => $contactDto->message,
        ] );

        $this->mailService->send( $contactEmail );

        // User email
        $userEmail = $this->mailService->prepareEmail(
            $contactDto->email,
            'Demande de contact reçue',
            'mails/contact/message-received.twig', [
            'name' => $contactDto->name,
        ] );

        $this->mailService->send( $userEmail );

        $contact = ( new Contact() )
            ->setName( $contactDto->name )
            ->setEmail( $contactDto->email )
            ->setSubject( $contactDto->subject )
            ->setMessage( $contactDto->message )
            ->setCreatedAt( new \DateTimeImmutable() );
        $contact->setImageFile($contactDto->imageFile);

        $this->contactRepository->save( $contact, true );
    }

}