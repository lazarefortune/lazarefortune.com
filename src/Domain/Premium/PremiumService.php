<?php

namespace App\Domain\Premium;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Notification\NotificationService;
use App\Domain\Premium\Entity\PremiumOffer;
use App\Infrastructure\Mailing\MailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PremiumService
{
    private const EXPIRATION_SOON_DAYS = 1;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MailService $mailService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
        private readonly NotificationService $notificationService,
    ) {
    }

    /**
     * Notifie par mail les utilisateurs dont l'abonnement premium expire bientôt.
     *
     * @param int $days nombre de jours avant expiration
     * @return int nombre d'utilisateurs notifiés
     */
    public function notifyUsersPremiumExpiringSoon(int $days = self::EXPIRATION_SOON_DAYS): int
    {
        $users = $this->userRepository->findUsersWithPremiumEndingSoon($days);

        if (empty($users)) {
            return 0;
        }

        $sentEmailsCount = 0;

        foreach ($users as $user) {
            try {
                $this->sendExpirationEmailNotification($user);
                $this->sendExpirationNotification($user);
                $this->logger->info(sprintf('Notification envoyée avec succès à %s', $user->getEmail()));
                $sentEmailsCount++;
            } catch (\Throwable $e) {
                $this->logger->error(sprintf('Erreur lors de l\'envoi du mail à %s : %s', $user->getEmail(), $e->getMessage()));
            }
        }

        return $sentEmailsCount;
    }

    /**
     * Prépare et envoie l'email d'expiration à l'utilisateur.
     */
    private function sendExpirationEmailNotification(User $user): void
    {
        $mail = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Votre abonnement arrive bientôt à expiration',
            'mails/account/premium/expire.twig',
            [
                'user' => $user,
                'account' => $this->generateAccountLink(),
            ]
        );

        $this->mailService->send($mail);
    }

    private function sendExpirationNotification(User $user): void
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $dateFormatted = $formatter->format($user->getPremiumEnd());

        $message = "Pour rappel, votre abonnement premium doit être renouvelé le {$dateFormatted}.";
        $this->notificationService->notifyUser($user, $message, $user);
    }

    public function notifyPremiumOfferNotification( User $user, PremiumOffer $premiumOffer): void
    {
        $this->sendPremiumOfferMail($user, $premiumOffer);
        $this->sendPremiumOfferNotification($user, $premiumOffer);
    }

    private function sendPremiumOfferNotification(User $user, PremiumOffer $premiumOffer): void
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $dateFormatted = $formatter->format($user->getPremiumEnd());

        $jourOuJours = ($premiumOffer->getDays() > 1) ? 'jours' : 'jour';

        $message = "Bonne nouvelle, {$premiumOffer->getUser()->getFullname()} vous a ajouté {$premiumOffer->getDays()} 
         {$jourOuJours} à votre abonnement Premium, il expire donc le {$dateFormatted}.";
        $this->notificationService->notifyUser($user, $message, $user);
    }

    private function sendPremiumOfferMail(User $user, PremiumOffer $premiumOffer): void
    {
        $mail = $this->mailService->prepareEmail(
            $user->getEmail(),
            "Bonne nouvelle {$user->getFullname()}! Tu as reçu un petit cadeau",
            'mails/account/premium/offer-days.twig',
            [
                'user' => $user,
                'account' => $this->generateAccountLink(),
                'premiumOffer' => $premiumOffer,
            ]
        );

        $this->mailService->send($mail);
    }


    /**
     * Génère un lien absolu vers le profil utilisateur.
     */
    private function generateAccountLink(): string
    {
        return $this->urlGenerator->generate(
            'app_account_profile',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
