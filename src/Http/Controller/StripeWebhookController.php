<?php

namespace App\Http\Controller;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Repository\AppointmentRepository;

//use App\Domain\Payment\Event\PaymentSuccessEvent;
//use App\Domain\Payment\Repository\PaymentRepository;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Entity\Transaction;
use App\Domain\Payment\Event\PaymentFailedEvent;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StripeWebhookController extends AbstractController
{
    private string $stripeWebhookSecret;

    public function __construct(
        readonly private EventDispatcherInterface $eventDispatcher,
        ParameterBagInterface                     $parameterBag,
    )
    {
        $this->stripeWebhookSecret = $parameterBag->get( 'stripe_webhook_secret' );
    }

    #[Route( '/stripe/webhooks', name: 'stripe_webhook' )]
    public function handle( Request $request ) : Response
    {
        $payload = $request->getContent();
        $sig_header = $request->headers->get( 'Stripe-Signature' );

        $event = null;

        try {
            // Vérifiez la signature de la requête avec la clé secrète d'endpoint de votre webhook
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $this->stripeWebhookSecret
            );
        } catch ( \UnexpectedValueException $e ) {
            // Signature invalide
            return new Response( 'Invalid request', 400 );
        } catch ( \Stripe\Exception\SignatureVerificationException $e ) {
            // Signature invalide
            return new Response( 'Invalid request', 400 );
        }

        // Gérez l'événement
        switch ( $event->type ) {
            case 'checkout.session.completed':
                $data = $event->data['object'];
                $sessionId = $data->id;

                // TODO: dispatch event to send email to user with invoice and change appointment status
                $paymentId = $data->metadata->payment_id;
                $this->eventDispatcher->dispatch( new PaymentSuccessEvent( $paymentId ) );
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                $paymentId = $paymentIntent->metadata->payment_id;
                break;
            case 'invoice.paid':
                $invoice = $event->data->object; // contains a StripeInvoice
                // Votre logique pour le paiement réussi
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                $paymentId = $paymentIntent->metadata->payment_id;
                $this->eventDispatcher->dispatch( new PaymentFailedEvent( $paymentId ) );
                break;
            // ... autres cas d'événements
        }

        return new Response( 'Received event', 200 );
    }
}

