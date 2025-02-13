<?php

namespace App\Infrastructure\Payment\Stripe;

use Stripe\Charge;
use Stripe\PaymentIntent;

class StripePaymentFactory
{
    public function __construct(private readonly StripeApi $api)
    {
    }

    public function createPaymentFromIntent(PaymentIntent $intent): StripePayment
    {
        /** @var Charge $charge */
        $charge = $intent->latest_charge;
        if (is_string($charge)) {
            $charge = $this->api->getCharge($charge);
        }
        // Vérifier et traiter la propriété balance_transaction
        if (is_string($charge->balance_transaction)) {
            $charge->balance_transaction = $this->api->getTransaction($charge->balance_transaction);
        }

        // Le paiement provient d'un abonnement et dispose d'une facture
        if ($intent->invoice) {
            $invoice = $this->api->getInvoice($intent->invoice);
            $subscription = $this->api->getSubscription((string) $invoice->subscription);
            $intent->metadata = $subscription->metadata;

            return new StripePayment($intent, $invoice);
        }

        // Le paiement provient d'une checkout session
        $session = $this->api->getCheckoutSessionFromIntent($intent->id);

        return new StripePayment($intent, $session);
    }
}