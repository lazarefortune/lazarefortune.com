<?php

namespace App\Domain\Payment;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Payment\Entity\Payment;
use App\Domain\Payment\Entity\Transaction;
use App\Domain\Payment\Event\PaymentSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentService
{
    private array $processors;

    public function __construct(
        array                                     $processors,
        private readonly EntityManagerInterface   $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->processors = $processors;
    }

    /**
     * @throws Exception
     */
    public function pay( float $amount, Appointment $appointment, string $paymentMethod ) : PaymentResult
    {
        $payment = new Payment();
        $payment->setPaymentMethod( $paymentMethod );
        $payment->setAmount( $amount );

        return $this->handlePaymentProcessing( $payment, $appointment );
    }

    /**
     * @throws Exception
     */
    private function handlePaymentProcessing( Payment $payment, Appointment $appointment ) : PaymentResult
    {
        foreach ( $this->processors as $processor ) {
            if ( $processor->supports( $payment ) ) {
                $transaction = $this->ensureTransaction( $appointment );

                $this->validatePayment( $payment, $appointment, $transaction );

                $payment = $this->persistPayment( $payment, $transaction );

                $paymentResult = $processor->processPayment( $payment, $appointment );
                if ( $paymentResult instanceof PaymentResultDone ) {
                    $this->eventDispatcher->dispatch( new PaymentSuccessEvent( $payment->getId() ) );
                }

                return $paymentResult;
            }
        }

        throw new Exception( "Méthode de paiement non supportée" );
    }

    private function ensureTransaction( Appointment $appointment ) : Transaction
    {
        $transaction = $appointment->getTransaction();
        if ( !$transaction ) {
            $transaction = new Transaction();
            $transaction->setAmount( $appointment->getTotal() );
            $transaction->setClient( $appointment->getClient() );
            $transaction->setStatus( Transaction::STATUS_PENDING );
            $transaction->addAppointment( $appointment );

            $this->entityManager->persist( $transaction );
            $this->entityManager->flush();
        }

        return $transaction;
    }

    /**
     * @throws Exception
     */
    private function validatePayment( Payment $payment, Appointment $appointment, Transaction $transaction ) : void
    {
        if ( $transaction->getStatus() === Transaction::STATUS_COMPLETED ) {
            throw new Exception( "La transaction a déjà été complétée" );
        }

        if ( $payment->getAmount() > $appointment->getTotal() ) {
            throw new Exception( "Le montant du paiement dépasse le montant de l'article" );
        }

        $totalPaid = $this->getAmountPaid( $appointment );
        if ( $totalPaid + $payment->getAmount() > $appointment->getTotal() ) {
            throw new Exception( "Le montant du paiement est trop élevé" );
        }
    }

    private function persistPayment( Payment $payment, Transaction $transaction ) : Payment
    {
        $existingPayment = $this->entityManager->getRepository( Payment::class )->findOneBy( [
            'transaction' => $transaction,
            'paymentMethod' => $payment->getPaymentMethod(),
            'amount' => $payment->getAmount(),
            'status' => Payment::STATUS_PENDING,
        ] );

        if ( !$existingPayment ) {
            $payment->setTransaction( $transaction );
            $this->entityManager->persist( $payment );
        } else {
            $payment = $existingPayment;
        }

        $this->entityManager->flush();
        return $payment;
    }

    private function getAmountPaid( Appointment $appointment ) : float
    {
        $transaction = $appointment->getTransaction();
        if ( !$transaction ) {
            return 0;
        }

        $payments = $this->entityManager->getRepository( Payment::class )->findBy( [
            'transaction' => $transaction,
            'status' => Payment::STATUS_SUCCESS,
        ] );

        return array_sum( array_map( static fn( Payment $p ) => $p->getAmount(), $payments ) );
    }
}
