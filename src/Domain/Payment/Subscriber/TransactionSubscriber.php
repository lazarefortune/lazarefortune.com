<?php

namespace App\Domain\Payment\Subscriber;

use App\Domain\Appointment\Event\AppointmentPaymentSuccessEvent;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Entity\Transaction;
use App\Domain\Payment\Event\TransactionCompletedEvent;
use App\Domain\Payment\Event\TransactionStatusCheckEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TransactionSubscriber implements EventSubscriberInterface
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
            TransactionStatusCheckEvent::class => 'onTransactionStatusCheck',
            TransactionCompletedEvent::class => 'onTransactionCompleted',
        ];
    }

    public function onTransactionCompleted( TransactionCompletedEvent $event ) : void
    {
        $transactionId = $event->getTransactionId();
        // find the transaction by id and update the status to success
        $transaction = $this->em->getRepository( Transaction::class )->find( $transactionId );
        if ( $transaction ) {
            $transaction->setStatus( Transaction::STATUS_COMPLETED );
            $this->em->persist( $transaction );
            $this->em->flush();

            $this->eventDispatcher->dispatch( new AppointmentPaymentSuccessEvent( $transaction->getAppointments() ) );
        }

    }

    public function onTransactionStatusCheck( TransactionStatusCheckEvent $event ) : void
    {
        $transactionId = $event->getTransactionId();

        // Find transaction by id and check the status
        $transaction = $this->em->getRepository( Transaction::class )->find( $transactionId );
        if ( $transaction ) {
            // get all payments with status success and check the transaction status
            $payments = $this->em->getRepository( Payment::class )->findBy( [
                'transaction' => $transaction,
                'status' => Payment::STATUS_SUCCESS,
            ] );
            // Check total payments
            $totalPaid = 0;
            foreach ( $payments as $payment ) {
                $totalPaid += $payment->getAmount();
            }

            if ( $totalPaid == $transaction->getAmount() ) {
                // dispatch an event to mark the transaction as completed
                $this->eventDispatcher->dispatch( new TransactionCompletedEvent( $transactionId ) );
            }
        }

        // if

    }
}