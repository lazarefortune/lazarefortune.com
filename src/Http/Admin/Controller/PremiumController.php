<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Premium\Entity\PremiumOffer;
use App\Domain\Premium\Event\PremiumOfferReceived;
use App\Domain\Premium\Repository\PremiumOfferRepository;
use App\Http\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted( "ROLE_SUPER_ADMIN" )]
#[Route( '/premium', name: 'premium_' )]
class PremiumController extends AbstractController
{
    public function __construct(
        private readonly UserRepository           $userRepository,
        private readonly PremiumOfferRepository   $premiumOfferRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    )
    {
    }

    #[Route( '/ajouter-jours', 'days_add', methods: ['POST'] )]
    public function addDays( Request $request ) : Response
    {
        $this->denyAccessUnlessGranted( 'ROLE_SUPER_ADMIN' );

        $days = (int)$request->request->get( 'days' );
        if ( $days <= 0 ) {
            $this->addFlash( 'danger', 'Le nombre de jours doit être positif.' );
            return $this->redirectToRoute( 'admin_users_index' );
        }
        $comment = $request->request->get('comment');
        $userId = $request->request->get( 'user_id' );

        if ( empty( $days ) || empty( $userId ) ) {
            $this->addFlash( 'danger', 'Erreur de données' );
            return $this->redirectToRoute( 'admin_users_index' );
        }

        $user = $this->userRepository->find( $userId );
        if ( !$user ) {
            $this->addFlash( 'danger', 'Erreur d\'utilisateur' );
            return $this->redirectToRoute( 'admin_users_index' );
        }

        $now = new \DateTimeImmutable( 'now', new \DateTimeZone( 'Europe/Paris' ) );

        $premiumEnd = $user->getPremiumEnd();

        if ( $premiumEnd === null ) {
            $premiumEnd = $now;
        }

        if ( $premiumEnd < $now ) {
            $premiumEnd = $now;
        }

        // On ajoute le nombre de jours
        $premiumEnd = $premiumEnd->modify( "+{$days} days" );

        // On met à jour la date de fin
        $user->setPremiumEnd( $premiumEnd );
        $this->userRepository->save( $user, true );

        $offer = new PremiumOffer();
        $offer
            ->setUser( $user )
            ->setAdmin( $this->getUser() )
            ->setDays( $days )
            ->setCreatedAt( new \DateTimeImmutable() )
            ->setComment($comment);

        $this->premiumOfferRepository->save( $offer, true );

        $this->eventDispatcher->dispatch( new PremiumOfferReceived( $user, $offer ), PremiumOfferReceived::class );

        $jourOuJours = ($offer->getDays() > 1) ? 'jours' : 'jour';
        $this->addFlash( 'success', "L'abonnement Premium de l'utilisateur a été prolongé de {$days} {$jourOuJours}." );

        return $this->redirectToRoute( 'admin_users_edit', [
            'id' => $user->getId(),
        ] );
    }
}