<?php

namespace App\Domain\Contact;

use App\Domain\Contact\Dto\ContactData;
use App\Domain\Contact\Entity\Contact;
use App\Domain\Contact\Repository\ContactRepository;
use App\Infrastructure\Mailing\MailService;

class ContactService
{

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly MailService       $mailService,
        private readonly string            $contactEmail
    )
    {
    }

    /**
     * Send contact message
     * @param ContactData $contactDto
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendContactMessage( ContactData $contactDto ) : void
    {
        if ( !$this->contactEmail ) {
            throw new \Exception( 'Invalid email' );
        }

        // Admin email
        $contactEmail = $this->mailService->prepareEmail(
            $this->contactEmail,
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

        $this->contactRepository->save( $contact, true );
    }

}