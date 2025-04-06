<?php

namespace App\Domain\Feedback\EventListener;

use App\Domain\Feedback\Entity\Feedback;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\Event\Event;

class FeedbackWorkflowListener
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    #[AsEventListener(event: 'workflow.feedback_status.completed.mark_resolved')]
    public function onFeedbackResolved(Event $event): void
    {
        /** @var Feedback $feedback */
        $feedback = $event->getSubject();

        $accountUrl = $this->urlGenerator->generate('app_account_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = $this->mailService->createEmail('mails/feedback/resolved.twig', [
            'feedback' => $feedback,
            'account' => $accountUrl,
        ])
            ->to($feedback->getEmail())
            ->subject('Merci pour ton retour, il est maintenant résolu')
            ->priority(Email::PRIORITY_HIGH);

        // Envoie l’email
        $this->mailService->send($email);
    }
}