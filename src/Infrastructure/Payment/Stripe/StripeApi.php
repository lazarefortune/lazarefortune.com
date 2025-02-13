<?php

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Premium\Entity\Plan;
use Stripe\BalanceTransaction;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Plan as StripePlan;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;

class StripeApi
{
    private readonly StripeClient $stripe;
    private array $taxes = [];

    public function __construct(string $privateKey)
    {
        Stripe::setApiVersion('2020-08-27');
        # $this->taxes = ['txr_1QrdtKKMIKbffFOxLVAZsbUj'];
        $this->taxes = ['txr_1Qs0pwA0DNjLouKa80XQQtBa'];
        if (str_contains($privateKey, 'live')) {
            $this->taxes = ['txr_1I7c7DFCMNgisvowdAol5zkl'];
        }
        $this->stripe = new StripeClient($privateKey);
    }

    /**
     * Crée un customer stripe et sauvegarde l'id dans l'utilisateur.
     */
    public function createCustomer(User $user): User
    {
        if ($user->getStripeId()) {
            return $user;
        }
        $client = $this->stripe->customers->create([
            'metadata' => [
                'user_id' => (string) $user->getId(),
            ],
            'email' => $user->getEmail(),
            'name' => $user->getFullname(),
        ]);
        $user->setStripeId($client->id);

        return $user;
    }

    public function getCustomer(string $customerId): Customer
    {
        return $this->stripe->customers->retrieve($customerId);
    }

    public function getInvoice(string $invoice): Invoice
    {
        return $this->stripe->invoices->retrieve($invoice);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    public function getPaymentIntent(string $id): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($id);
    }

    /**
     * Crée une session et renvoie l'URL de paiement.
     */
    public function createSuscriptionSession(User $user, Plan $plan, string $url): string
    {
        $session = $this->stripe->checkout->sessions->create([
            'cancel_url' => $url,
            'success_url' => $url.'?success=1',
            'mode' => 'subscription',
            'payment_method_types' => [
                'card',
            ],
            'subscription_data' => [
                'metadata' => [
                    'plan_id' => $plan->getId(),
                ],
            ],
            'customer' => $user->getStripeId(),
            'line_items' => [
                [
                    'price' => $plan->getStripeId(),
                    'quantity' => 1,
                    'dynamic_tax_rates' => $this->taxes,
                ],
            ],
        ]);

        return $session->id;
    }

    public function createPaymentSession(User $user, Plan $plan, string $url): string
    {
        $session = $this->stripe->checkout->sessions->create([
            'cancel_url' => $url,
            'success_url' => $url.'?success=1',
            'mode' => 'payment',
            'payment_method_types' => [
                'card',
            ],
            'customer' => $user->getStripeId(),
            'metadata' => [
                'plan_id' => $plan->getId(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'plan_id' => $plan->getId(),
                ],
            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $plan->getName(),
                        ],
                        'unit_amount' => $plan->getPrice() * 100,
                    ],
                    'quantity' => 1,
                    'dynamic_tax_rates' => $this->taxes,
                ],
            ],
        ]);

        return $session->id;
    }

    /**
     * Renvoie l'url du profil d'abonnement stripe.
     */
    public function getBillingUrl(User $user, string $returnUrl): string
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $user->getStripeId(),
            'return_url' => $returnUrl,
        ])->url;
    }

    public function getPlan(string $id): StripePlan
    {
        return $this->stripe->plans->retrieve($id);
    }

    public function getCheckoutSessionFromIntent(string $paymentIntent): Session
    {
        /** @var Session[] $sessions */
        $sessions = $this->stripe->checkout->sessions->all(['payment_intent' => $paymentIntent])->data;

        return $sessions[0];
    }

    public function getTransaction(string $id): BalanceTransaction
    {
        return $this->stripe->balanceTransactions->retrieve($id);
    }

    public function getCharge( string|\Stripe\Charge|null $latest_charge ) : \Stripe\Charge
    {
        return $this->stripe->charges->retrieve($latest_charge);
    }
}