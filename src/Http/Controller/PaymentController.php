<?php

namespace App\Http\Controller;

use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Payment\Entity\Transaction;
use App\Domain\Payment\PaymentService;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService        $paymentService,
        private readonly AppointmentRepository $appointmentRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route( '/payment', name: 'payment' )]
    public function processPayment( Request $request ) : Response
    {
        $paymentMethod = ( $request->get( 'pm' ) ) ? $request->get( 'pm' ) : 'stripe';

        $appointment = $this->appointmentRepository->findBy( ['id' => 5] )[0];

        $url = $this->paymentService->pay( 20, $appointment, $paymentMethod );

        if ( $url ) {
            return $this->redirect( $url );
        }

        return $this->redirectToRoute( 'app_payment_result', ['id' => 1] );
    }

    #[Route( '/payment/result/{id<\d+>}', name: 'payment_result' )]
    public function paymentResult( Request $request ) : Response
    {
        $isPaid = ( $request->get( 'success' ) === '1' ) ? true : false;

        return $this->render( 'payment/result.html.twig', [
            'isPaid' => $isPaid,
        ] );
    }
}