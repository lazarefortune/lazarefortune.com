<?php

namespace App\Http\Api\Controller;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Service\AppointmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/appointments', name: 'appointments_' )]
class ApiAppointmentController extends AbstractController
{
    public function __construct( private readonly AppointmentService $appointmentService )
    {
    }

    #[Route( '/{date}', name: 'appointments_date', methods: ['GET'] )]
    public function getAppointmentsByDate( string $date ) : JsonResponse
    {
        $date = new \DateTime( $date );
        // Si la date est passée, on ne retourne rien
        if ( $date < new \DateTime() ) {
            return $this->json( [] );
        }
        /** @var Appointment[] $appointments */
        $appointments = $this->appointmentService->getAppointmentsByDate( $date );
        $data = [];
        /** @var Appointment $appointment */
        foreach ( $appointments as $appointment ) {
            $data[] = array(
                'id' => $appointment->getId(),
                'startTime' => $appointment->getStartTime()->format( 'H:i' ),
                'endTime' => $appointment->getEndTime()->format( 'H:i' ),
            );
        }

        return $this->json( $data );
    }
}