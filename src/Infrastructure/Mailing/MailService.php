<?php

namespace App\Infrastructure\Mailing;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailService
{

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment     $twig,
        private string                   $senderEmail,
        private string                   $senderName,
    )
    {
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createEmail( string $template, array $data ) : Email
    {

        $this->twig->addGlobal( 'format', 'html' );
        $html = $this->twig->render( $template, array_merge( $data, ['layout' => 'mails/base.html.twig'] ) );
        $this->twig->addGlobal( 'format', 'txt' );
        $text = $this->twig->render( $template, array_merge( $data, ['layout' => 'mails/base.text.twig'] ) );

        return ( new Email() )
            ->from( new Address( $this->senderEmail, $this->senderName ) )
            ->html( $html )
            ->text( $text );
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function prepareEmail(
        string $emailTo,
        string $subject,
        string $view,
        array  $data,
    ) : Email
    {
        return $this->createEmail( $view, $data )
            ->to( $emailTo )
            ->subject( $subject );
    }

    /**
     * Send email
     * @param Email $email
     * @return void
     */
    public function send( Email $email ) : void
    {
        try {
            $this->mailer->send( $email );
        } catch ( TransportExceptionInterface $e ) {
            $e->getMessage();
        }
    }
}