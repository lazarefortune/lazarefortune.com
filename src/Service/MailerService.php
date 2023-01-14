<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct( MailerInterface $mailer )
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMail( $to, $name, $subject, $message ) : void
    {
        $email = (new TemplatedEmail())
            ->from('service@lazarefortune.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('layouts/emails/contact.html.twig')
            ->context([
                'name' => $name,
                'message' => $message,
            ]);

        $this->mailer->send($email);
    }
}