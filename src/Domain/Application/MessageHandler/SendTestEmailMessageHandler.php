<?php
namespace App\Domain\Application\MessageHandler;


use App\Domain\Application\Message\SendTestEmailMessage;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsMessageHandler]
class SendTestEmailMessageHandler
{
    public function __construct(
        private readonly MailService $mailService
    ) {}

    public function __invoke( SendTestEmailMessage $message) : void
    {
        try {
            $email = $this->mailService->createEmail('mails/test.twig', [])
                ->to($message->getEmail())
                ->subject('Email de test');

            $this->mailService->send($email);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            // Gestion des erreurs éventuelles
            error_log("Erreur lors de l'envoi d'email : " . $e->getMessage());
        }
    }
}