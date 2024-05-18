<?php

namespace App\Http\Controller;

use App\Domain\Appointment\Dto\AppointmentData;
use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Payment\PaymentResultUrl;
use App\Domain\Payment\PaymentService;
use App\Infrastructure\Payment\Cash\CashPaymentProcessor;
use App\Infrastructure\Payment\Stripe\StripePaymentProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/rendez-vous', name: 'appointment_' )]
class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService     $paymentService,
        private readonly AppointmentService $appointmentService,
    )
    {
    }

    #[Route( '/gestion/{token}', name: 'manage' )]
    public function appointmentDetails( string $token ) : Response
    {
        $appointment = $this->getAppointmentOrNull( $token );
        $amountPaid = $appointment->getAmountPaid();
        
        $paymentMethods = [
            StripePaymentProcessor::PAYMENT_METHOD,
            CashPaymentProcessor::PAYMENT_METHOD,
        ];

        return $this->render( 'appointment/manage-view.html.twig', [
            'appointment' => $appointment,
            'amountPaid' => $amountPaid,
            'paymentMethods' => $paymentMethods,
            'amountDue' => $appointment->getRemainingAmount(),
        ] );
    }

    #[Route( '/paiement/demarrer', name: 'payment_process', methods: ['POST'] )]
    public function paymentProcess( Request $request ) : Response
    {
        $paymentMethod = $request->request->get( 'payment-method' );
        $amount = $request->request->get( 'amount' );
        $appointmentToken = $request->request->get( 'appointment-token' );
        $paymentStrategy = $request->request->get( 'payment-strategy' );

        $appointment = $this->getAppointmentOrNull( $appointmentToken );

        if ( $amount ) {
            $amount = (int)( $amount * 100 );
        } else {
            if ( $paymentStrategy == 'payment-partial' ) {
                $amount = ( $appointment->getTotal() / 2 );
            } else {
                $amount = $appointment->getRemainingAmount();
            }
        }

        try {
            $paymentResult = $this->paymentService->pay( $amount, $appointment, $paymentMethod );

            if ( $paymentResult instanceof PaymentResultUrl ) {
                return $this->redirect( $paymentResult->getUrl() );
            }

            $this->addFlash( 'success', 'Le paiement a bien été effectué' );
            return $this->redirectToRoute( 'app_appointment_manage', ['token' => $appointment->getAccessToken()] );
        } catch ( \Exception $e ) {
            $this->addFlash( 'danger', $e->getMessage() );
            return $this->redirectToRoute( 'app_appointment_manage', ['token' => $appointment->getAccessToken()] );
        }
    }

    #[Route( '/gestion/{token}/modification', name: 'manage_edit', methods: ['GET', 'POST'] )]
    public function editManagement( string $token, Request $request ) : Response
    {
        $appointment = $this->getAppointmentOrNull( $token );
        $appointmentUpdateDto = new AppointmentManageUpdateData( $appointment );
        $form = $this->createForm( AppointmentManageUpdateForm::class, $appointmentUpdateDto );
        $form->handleRequest( $request );

        if ( $this->handleAppointmentUpdateForm( $form, $appointment ) ) {
            $this->addFlash( 'success', 'La réservation a bien été modifiée' );
            return $this->redirectToRoute( 'app_appointment_manage', ['token' => $token] );
        }

        return $this->render( 'appointment/manage-update.html.twig', [
            'form' => $form->createView(),
            'appointment' => $appointment,
        ] );
    }

    private function getAppointmentOrNull( string $token ) : ?Appointment
    {
        $appointment = $this->appointmentService->getAppointmentByAccessToken( $token );
        if ( !$appointment ) {
            return null;
        }
        return $appointment;
    }

    private function handleAppointmentUpdateForm( $form, Appointment $appointment ) : bool
    {
        if ( $form->isSubmitted() && $form->isValid() ) {
            $appointmentDto = new AppointmentData( $appointment );
            $appointmentDto->date = $form->getData()->date;
            $appointmentDto->time = $form->getData()->time;
            $this->appointmentService->updateAppointmentWithDto( $appointmentDto );

            return true;
        }
        return false;
    }

    #[Route( '/paiement/demarrer/{id<\d+>}', name: 'payment_start' )]
    #[ParamConverter( 'appointment', options: ['mapping' => ['id' => 'id']] )]
    public function startPayment( Appointment $appointment, Request $request ) : Response
    {
        $data = $request->request->all();

        if ( isset( $data['amount'] ) && isset( $data['paymentMethod'] ) ) {
            $amount = ( $data['amount'] ) ? (int)$data['amount'] : $appointment->getTotal();
            $paymentMethod = ( $data['paymentMethod'] === StripePaymentProcessor::PAYMENT_METHOD ) ? StripePaymentProcessor::PAYMENT_METHOD : CashPaymentProcessor::PAYMENT_METHOD;
        } else {
            $amountDue = $appointment->getTotal() - $appointment->getAmountPaid();
            if ( $amountDue > 0 ) {
                $amount = $amountDue;
            } else {
                $amount = $appointment->getTotal();
            }
            $paymentMethod = StripePaymentProcessor::PAYMENT_METHOD;
        }


        try {
            $paymentResult = $this->paymentService->pay( $amount, $appointment, $paymentMethod );

            if ( $paymentResult instanceof PaymentResultUrl ) {
                return $this->redirect( $paymentResult->getUrl() );
            }

            $this->addFlash( 'success', 'Le paiement a bien été effectué' );
            return $this->redirectToRoute( 'app_appointment_manage', ['token' => $appointment->getAccessToken()] );
        } catch ( \Exception $e ) {
            $this->addFlash( 'danger', $e->getMessage() );
            return $this->redirectToRoute( 'app_appointment_manage', ['token' => $appointment->getAccessToken()] );
        }
    }

    #[Route( '/paiement/resultat/{id<\d+>}', name: 'payment_result' )]
    public function paymentResult( Request $request, Appointment $appointment ) : Response
    {
        $paymentSuccess = $request->query->get( 'success' ) === '1';
        $status = $request->query->get( 'success' ) === '1' ? 'success' : 'failure';

        ( $paymentSuccess ) ? $this->addFlash( 'success', 'Le paiement a bien été effectué' ) : $this->addFlash( 'danger', 'Le paiement a échoué' );
        return $this->redirectToRoute( 'app_appointment_manage', ['token' => $appointment->getAccessToken()] );
    }
}
