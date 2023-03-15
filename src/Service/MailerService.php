<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private MailerInterface $mailer;
    private $params;

    public function __construct( MailerInterface $mailer, ContainerBagInterface $params )
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function sendMail( $to, $subject, $template, $context ) : void
    {
        if ( ! $this->params->has( 'app.mailer.sender' ) ) {
            throw new \Exception( 'app.mailer.sender is not defined in config/services.yaml' );
        }
        $sender = $this->params->get( 'app.mailer.sender' );

        $email = ( new TemplatedEmail() )
            ->from( $sender )
            ->to( $to )
            ->subject( $subject )
            ->htmlTemplate( $template )
            ->context( $context );

        $this->mailer->send( $email );
    }
}