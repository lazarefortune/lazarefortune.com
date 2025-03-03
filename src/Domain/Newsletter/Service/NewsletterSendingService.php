<?php

namespace App\Domain\Newsletter\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Newsletter\Entity\Newsletter;
use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Domain\Newsletter\Enum\NewsletterStatus;
use App\Domain\Newsletter\Enum\NewsletterTargetGroupOptions;
use App\Domain\Newsletter\Repository\NewsletterRepository;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterSendingService
{
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
        private readonly UserRepository $userRepository,
        private readonly NewsletterSubscriberRepository $subscriberRepository,
        private readonly MailService $mailService,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Récupère les newsletters à envoyer et lance l'envoi
     */
    public function sendScheduledNewsletters(): void
    {
        // 1) Récupérer les newsletters prêtes à être envoyées
        //    - statut = PENDING
        //    - isDraft = false
        //    - sendAt <= now
        $newsletters = $this->newsletterRepository->findReadyToSend();

        // 2) Pour chacune, envoyer la newsletter
        foreach ($newsletters as $newsletter) {
            $this->sendNewsletter($newsletter);
        }
    }

    /**
     * Envoie une newsletter à son public cible et met à jour le statut
     */
    public function sendNewsletter(Newsletter $newsletter): void
    {

        // Si la cible est ALL, on envoie aux utilisateurs ET aux abonnés
        // Si la cible est USERS, on envoie uniquement aux utilisateurs
        // Si la cible est SUBSCRIBERS, on envoie uniquement aux visiteurs

        if (
            $newsletter->getTargetGroup() === NewsletterTargetGroupOptions::ALL ||
            $newsletter->getTargetGroup() === NewsletterTargetGroupOptions::USERS
        ) {
            $this->sendToUsers($newsletter);
        }

        if (
            $newsletter->getTargetGroup() === NewsletterTargetGroupOptions::ALL ||
            $newsletter->getTargetGroup() === NewsletterTargetGroupOptions::SUBSCRIBERS
        ) {
            $this->sendToSubscribers($newsletter);
        }

        // Mettre à jour le statut en SENT après l'envoi
        $newsletter->setStatus(NewsletterStatus::SENT);
        $this->em->flush();
    }

    private function sendToUsers(Newsletter $newsletter): void
    {
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.isNewsletterSubscribed = true')
            ->getQuery()
            ->getResult();

        /** @var User $user */
        foreach ($users as $user) {
            // Générer le lien de désabonnement en se basant sur le token de l'utilisateur
            $unsubscribeUrl = $this->urlGenerator->generate(
                'app_newsletter_unsubscribe_user',
                ['token' => $user->getUnsubscribeNewsletterToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $mail = $this->mailService->prepareEmail(
                $user->getEmail(),
                $newsletter->getSubject(),
                'mails/admin/newsletter/newsletter.twig',
                [
                    'newsletter'    => $newsletter,
                    'unsubscribeUrl'=> $unsubscribeUrl,
                ]
            );
            $this->mailService->send($mail);
        }
    }

    private function sendToSubscribers(Newsletter $newsletter): void
    {
        $subscribers = $this->subscriberRepository->createQueryBuilder('s')
            ->where('s.isSubscribed = true')
            ->getQuery()
            ->getResult();

        /** @var NewsletterSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            // Générer le lien de désabonnement en se basant sur le token du visiteur
            $unsubscribeUrl = $this->urlGenerator->generate(
                'app_newsletter_unsubscribe_subscriber',
                ['token' => $subscriber->getUnsubscribeToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $mail = $this->mailService->prepareEmail(
                $subscriber->getEmail(),
                $newsletter->getSubject(),
                'mails/admin/newsletter/newsletter.twig',
                [
                    'newsletter'    => $newsletter,
                    'unsubscribeUrl'=> $unsubscribeUrl,
                ]
            );
            $this->mailService->send($mail);
        }
    }

}
