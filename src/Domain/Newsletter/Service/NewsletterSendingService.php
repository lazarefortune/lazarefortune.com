<?php

namespace App\Domain\Newsletter\Service;

use App\Domain\Newsletter\Entity\Newsletter;
use App\Domain\Newsletter\Enum\NewsletterStatus;
use App\Domain\Newsletter\Enum\NewsletterTargetGroupOptions;
use App\Domain\Newsletter\Repository\NewsletterRepository;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;

class NewsletterSendingService
{
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
        private readonly UserRepository $userRepository,
        private readonly NewsletterSubscriberRepository $subscriberRepository,
        private readonly MailService $mailService,
        private readonly EntityManagerInterface $em
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
        $now = new \DateTimeImmutable();
        $newsletters = $this->newsletterRepository->createQueryBuilder('n')
            ->where('n.status = :status')
            ->andWhere('n.isDraft = false')
            ->andWhere('n.sendAt <= :now')
            ->setParameter('status', NewsletterStatus::PENDING)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

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
        // 1) Récupérer la liste d'emails
        $emails = $this->getTargetEmails($newsletter->getTargetGroup());

        // 2) Envoyer le mail à chaque adresse
        foreach ($emails as $email) {
            $mail = $this->mailService->prepareEmail(
                $email,
                $newsletter->getSubject(),
                'mails/admin/newsletter/newsletter.twig',
                [
                    'newsletter' => $newsletter,
                ]
            );
            $this->mailService->send($mail);
        }

        // 3) Mettre à jour la newsletter en base
        $newsletter->setStatus(NewsletterStatus::SENT);
        $this->em->flush();
    }

    /**
     * Selon la cible (ALL / USERS / SUBSCRIBERS), on récupère la bonne liste d'emails
     */
    private function getTargetEmails(NewsletterTargetGroupOptions $targetGroup): array
    {
        return match ($targetGroup) {
            NewsletterTargetGroupOptions::ALL => $this->getAllEmails(),
            NewsletterTargetGroupOptions::USERS => $this->getUsersEmails(),
            NewsletterTargetGroupOptions::SUBSCRIBERS => $this->getSubscribersEmails(),
        };
    }

    private function getAllEmails(): array
    {
        $users = $this->getUsersEmails();
        $subs = $this->getSubscribersEmails();

        // Combiner les deux, en supprimant les doublons si besoin
        $emails = array_unique(array_merge($users, $subs));

        return $emails;
    }

    private function getUsersEmails(): array
    {
        // On récupère juste ceux qui sont abonnés à la newsletter
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.isNewsletterSubscribed = true')
            ->getQuery()
            ->getResult();

        // Extraire l'email dans un tableau simple
        return array_map(fn($user) => $user->getEmail(), $users);
    }

    private function getSubscribersEmails(): array
    {
        // On ne prend que ceux qui sont encore abonnés
        $subs = $this->subscriberRepository->createQueryBuilder('s')
            ->where('s.isSubscribed = true')
            ->getQuery()
            ->getResult();

        return array_map(fn($sub) => $sub->getEmail(), $subs);
    }
}
