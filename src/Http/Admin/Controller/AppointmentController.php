<?php

namespace App\Http\Admin\Controller;

use App\Domain\Appointment\Dto\AppointmentData;
use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Form\AppointmentForm;
use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Payment\PaymentResultUrl;
use App\Domain\Payment\PaymentService;
use App\Domain\Prestation\Entity\Prestation;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted( 'ROLE_SUPER_ADMIN' )]
#[Route( '/rendez-vous', name: 'appointment_' )]
class AppointmentController extends AbstractController
{

    public function __construct(
        private readonly AppointmentService     $appointmentService,
        private readonly EntityManagerInterface $em,
        private readonly PaymentService         $paymentService,
    )
    {
    }

    #[Route( '/', name: 'index' )]
    public function index() : Response
    {
        $appointments = $this->appointmentService->getAppointmentsPaginated( $page = 1, $limit = 10 );
        return $this->render( 'admin/appointment/index.html.twig', [
            'appointments' => $appointments,
        ] );
    }

    #[Route( '/new', name: 'new', methods: ['GET', 'POST'] )]
    public function new( Request $request ) : Response
    {
        [$form, $response] = $this->createFormAppointment( $request );

        if ( $response ) {
            $this->addFlash( 'success', 'Votre rendez-vous a bien été enregistré.' );
            return $response;
        }

        return $this->render( 'admin/appointment/new.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    private function createFormAppointment( Request $request, Appointment $appointment = null ) : array
    {
        $form = $this->createForm( AppointmentForm::class, $appointment );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $slotId = $form->get( 'slot' )->getData();
            if ( !$slotId ) {
                $form->get( 'slot' )->addError( new FormError( 'Veuillez sélectionner un créneau.' ) );
                return [$form, null];
            }

            $appointmentData = new AppointmentData(
                client: $form->get( 'client' )->getData(),
                prestation: $form->get( 'prestation' )->getData(),
                date: $form->get( 'date' )->getData(),
                autoConfirm: $form->get( 'autoConfirm' )->getData(),
                slotId: $form->get( 'slot' )->getData()
            );

            $appointment = $this->appointmentService->addOrUpdateAppointment( $appointmentData );
            $this->addFlash( 'success', 'Rendez-vous enregistré avec succès.' );

            return [$form, $this->redirectToRoute( 'admin_appointment_index' )];
        }

        return [$form, null];
    }

    #[Route( '/slots', name: 'slots', methods: ['GET'] )]
    public function getSlots( Request $request ) : Response
    {
        $date = $request->query->get( 'date' );
        $prestationId = $request->query->get( 'prestationId' );
        $prestation = $this->em->getRepository( Prestation::class )->find( $prestationId );

        if ( !$prestation ) {
            return new Response( 'Prestation introuvable.', Response::HTTP_NOT_FOUND );
        }

        $date = new \DateTime( $date );

        $appointment = new Appointment();
        $appointment->setDate( $date );
        $appointment->setPrestation( $prestation );

        $form = $this->createForm( AppointmentForm::class, $appointment );

        return $this->render( 'admin/appointment/blocs/_slots.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'show', methods: ['GET'] )]
    public function show( Appointment $appointment = null ) : Response
    {
        if ( !$appointment ) {
            $this->addFlash( 'danger', 'Rendez-vous introuvable.' );
            return $this->redirectToRoute( 'admin_appointment_index' );
        }

        return $this->render( 'admin/appointment/show.html.twig', [
            'appointment' => $appointment,
        ] );
    }

    #[Route( '/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'POST'] )]
    public function edit( Request $request, Appointment $appointment = null ) : Response
    {
        if ( !$appointment ) {
            $this->addFlash( 'danger', 'Rendez-vous introuvable.' );
            return $this->redirectToRoute( 'admin_appointment_index' );
        }

        [$form, $response] = $this->createFormAppointment( $request, $appointment );

        if ( $response ) {
            $this->addFlash( 'success', 'Votre rendez-vous a bien été modifié.' );
            return $response;
        }

        return $this->render( 'admin/appointment/update.html.twig', [
            'form' => $form->createView(),
        ] );
    }

    #[Route( '/{id<\d+>}/confirm', name: 'confirm', methods: ['GET', 'POST'] )]
    public function confirm( Request $request, Appointment $appointment = null ) : Response
    {
        if ( !$appointment ) {
            $this->addFlash( 'danger', 'Rendez-vous introuvable.' );
            return $this->redirectToRoute( 'admin_appointment_index' );
        }

        if ( $this->isCsrfTokenValid( 'confirm_appointment' . $appointment->getId(), $request->request->get( '_token' ) ) ) {
            $this->appointmentService->confirmAppointment( $appointment );

            $this->addFlash( 'success', 'Rendez-vous confirmé avec succès.' );
            return $this->redirectToRoute( 'admin_appointment_show', ['id' => $appointment->getId()] );
        }

        return $this->redirectToRoute( 'admin_appointment_index' );
    }

    #[Route( '/{id<\d+>}/cancel', name: 'cancel' )]
    public function cancel( Request $request, Appointment $appointment = null ) : Response
    {
        if ( !$appointment ) {
            $this->addFlash( 'danger', 'Rendez-vous introuvable.' );
            return $this->redirectToRoute( 'admin_appointment_index' );
        }

        $this->appointmentService->cancelAppointment( $appointment );
        if ( $this->isCsrfTokenValid( 'cancel_appointment' . $appointment->getId(), $request->request->get( '_token' ) ) ) {

            $this->addFlash( 'success', 'Rendez-vous annulé avec succès.' );
            return $this->redirectToRoute( 'admin_appointment_show', ['id' => $appointment->getId()] );
        }

        return $this->redirectToRoute( 'admin_appointment_index' );
    }

    #[Route( '/{id<\d+>}/delete', name: 'delete', methods: ['POST'] )]
    public function delete( Request $request, Appointment $appointment ) : Response
    {
        if ( $this->isCsrfTokenValid( 'delete' . $appointment->getId(), $request->request->get( '_token' ) ) ) {
            $this->appointmentService->deleteAppointment( $appointment );

            $this->addFlash( 'success', 'Rendez-vous supprimé avec succès.' );
        }

        return $this->redirectToRoute( 'admin_appointment_index', [], Response::HTTP_SEE_OTHER );
    }

    #[Route( '/{id<\d+>}/paiement', name: 'payment', methods: ['GET', 'POST'] )]
    public function payment( Request $request, Appointment $appointment ) : Response
    {
        if ( $appointment->isStatusCanceled() ) {
            $this->addFlash( 'danger', 'Le rendez-vous a été annulé.' );
            return $this->redirectToRoute( 'admin_appointment_show', ['id' => $appointment->getId()] );
        }

        if ( $appointment->isPaid() ) {
            $this->addFlash( 'danger', 'Le rendez-vous a déjà été payé.' );
            return $this->redirectToRoute( 'admin_appointment_show', ['id' => $appointment->getId()] );
        }

        if ( $request->isMethod( 'POST' ) ) {
            try {
                $paymentMethod = $request->get( 'payment-method' ) ?? 'stripe';
                $paymentStrategy = $request->get( 'payment-strategy' ) ?? 'payment-full';

                $amount = $paymentStrategy == 'payment-partial'
                    ? (int)( str_replace( ',', '.', $request->get( 'amount' ) ?? $appointment->getRemainingAmount() ) * 100 )
                    : $appointment->getRemainingAmount();

                $paymentResult = $this->paymentService->pay( $amount, $appointment, $paymentMethod );

                if ( $paymentResult instanceof PaymentResultUrl ) {
                    return $this->redirect( $paymentResult->getUrl() );
                }

                $this->addFlash( 'success', 'Le paiement a bien été effectué' );
                return $this->redirectToRoute( 'admin_appointment_show', ['id' => $appointment->getId()] );
            } catch ( \Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
            }
        }

        return $this->render( 'admin/appointment/payment.html.twig', [
            'appointment' => $appointment,
        ] );
    }

}
