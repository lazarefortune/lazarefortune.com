<?php

namespace App\Domain\Payment\Subscriber;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Event\PaymentFailedEvent;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use App\Domain\Payment\Event\TransactionStatusCheckEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaymentSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            PaymentSuccessEvent::class => 'onPaymentSuccess',
            PaymentFailedEvent::class => 'onPaymentFailed',
        ];
    }

    public function onPaymentSuccess( PaymentSuccessEvent $event ) : void
    {
        $paymentId = $event->getPaymentId();
        // find the payment by id and update the status to success
        $payment = $this->em->getRepository( Payment::class )->find( $paymentId );
        if ( $payment ) {
            $payment->setStatus( Payment::STATUS_SUCCESS );
            $payment->setUpdatedAt( new \DateTime() );
            $payment->setSessionId( null );
            $this->em->persist( $payment );
            $this->em->flush();
        }

        // TODO: refactor this (update amount paid in appointment)
        /** @var Appointment $appointment */
        $appointment = $payment->getTransaction()->getAppointments()->first();
        $appointment->setAmountPaid( $appointment->getAmountPaid() + $payment->getAmount() );
        $this->em->persist( $appointment );
        $this->em->flush();

        // Dispatch an event to check if the transaction was successful
        $this->eventDispatcher->dispatch( new TransactionStatusCheckEvent( $payment->getTransaction()->getId() ) );

        // send an email to the user
    }

    public function onPaymentFailed()
    {
    }


}