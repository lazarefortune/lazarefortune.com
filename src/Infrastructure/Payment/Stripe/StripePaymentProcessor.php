<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Auth\Entity\User;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\PaymentProcessorInterface;
use App\Domain\Payment\PaymentResultUrl;
use App\Infrastructure\Payment\Stripe\StripeApi;
use App\Domain\Payment\TransactionItemInterface;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripePaymentProcessor implements PaymentProcessorInterface
{

    const PAYMENT_METHOD = 'stripe';

    public function __construct(
        private readonly StripeApi              $stripeApi,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface  $urlGenerator,
    )
    {
    }
    
    public function supports( Payment $payment ) : bool
    {
        return $payment->getPaymentMethod() === self::PAYMENT_METHOD;
    }

    /**
     * @throws ApiErrorException
     */
    public function processPayment( Payment $payment, Appointment $appointment ) : PaymentResultUrl
    {
        // Si le montant est inférieur à 0.50€ on bloque le paiement
        if ( $payment->getAmount() < 0.50 ) {
            throw new \InvalidArgumentException( 'Le montant est trop faible pour être payé.' );
        }

        // On s'assure que le client existe dans Stripe
        $this->ensureCustomerExists( $payment->getTransaction()->getClient() );

        // Si la session est null, on la crée
        if ( !$payment->getSessionId() ) {
            $url = $this->urlGenerator->generate( 'app_payment_result', [
                'id' => $payment->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL );

            $sessionId = $this->stripeApi->createPaymentSession( $payment, $appointment, $url );

            $payment->setSessionId( $sessionId )
                ->setUpdatedAt( new \DateTimeImmutable() );

            $this->entityManager->persist( $payment );
            $this->entityManager->flush();
        }

        // sinon on la récupère
        $session = $this->stripeApi->getSession( $payment->getSessionId() );

        // Si la session est invalide on la met à jour
        if ( $this->isSessionInvalid( $session ) ) {
            $url = $this->urlGenerator->generate( 'app_payment_result', [
                'id' => $payment->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL );

            $sessionId = $this->stripeApi->createPaymentSession( $payment, $appointment, $url );

            $payment->setSessionId( $sessionId )
                ->setUpdatedAt( new \DateTimeImmutable() );

            $this->entityManager->persist( $payment );
            $this->entityManager->flush();

            $session = $this->stripeApi->getSession( $payment->getSessionId() );
        }

        return new PaymentResultUrl( false, 'Payment redirected to Stripe', $session->url );
    }

    private function isSessionInvalid( $session ) : bool
    {
        return 'paid' === $session->payment_status || $session->expires_at < time();
    }

    /**
     * Assure que le client existe dans Stripe.
     * @param User $client
     * @return User
     * @throws ApiErrorException
     */
    private function ensureCustomerExists( User $client ) : void
    {
        if ( !$client->getStripeId() || $this->isCustomerDeleted( $client->getStripeId() ) ) {
            $customerData = [
                'userId' => $client->getId(),
                'name' => $client->getFullname(),
                'email' => $client->getEmail(),
                'stripeId' => $client->getStripeId(),
            ];
            $clientStripeId = $this->stripeApi->createCustomer( $customerData );
            $client->setStripeId( $clientStripeId );
            $this->entityManager->persist( $client );
            $this->entityManager->flush();
        }
    }

    /**
     * Renvoie vrai si le client a été supprimé de Stripe.
     * @param string $clientStripeId
     * @return bool
     * @throws ApiErrorException
     */
    private function isCustomerDeleted( string $clientStripeId ) : bool
    {
        $customerStripe = $this->stripeApi->getCustomer( $clientStripeId );
        return $customerStripe->isDeleted();
    }

}