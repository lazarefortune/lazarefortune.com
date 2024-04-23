<?php

namespace App\Domain\Appointment\Service;

use App\Domain\Application\Service\OptionService;
use App\Domain\Appointment\Dto\AppointmentData;
use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Entity\Slot;
use App\Domain\Appointment\Event\CanceledAppointmentEvent;
use App\Domain\Appointment\Event\ConfirmedAppointmentEvent;
use App\Domain\Appointment\Event\CreatedAppointmentEvent;
use App\Domain\Appointment\Event\UpdatedAppointmentEvent;
use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Holiday\HolidayService;
use App\Domain\Prestation\Entity\Prestation;
use App\Helper\Paginator\PaginatorInterface;
use App\Infrastructure\Security\TokenGeneratorService;
use DateTime;
use DateTimeInterface;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository    $appointmentRepository,
        private readonly HolidayService           $holidayService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenGeneratorService    $tokenGeneratorService
    )
    {
    }

    public function getAppointmentsGroupedByStatus() : array
    {
        $appointments = $this->appointmentRepository->findAll();
        $today = new DateTime();
        $appointmentsByStatus = ['today' => [], 'future' => [], 'past' => []];

        foreach ( $appointments as $appointment ) {
            $appointmentDate = $appointment->getDate();
            $formattedDate = $appointmentDate->format( 'Y-m-d' );
            $formattedToday = $today->format( 'Y-m-d' );

            if ( $formattedDate === $formattedToday ) {
                $appointmentsByStatus['today'][] = $appointment;
            } elseif ( $appointmentDate > $today ) {
                $appointmentsByStatus['future'][] = $appointment;
            } else {
                $appointmentsByStatus['past'][] = $appointment;
            }
        }

        // Optionnellement, trier les rendez-vous futurs et passés
        usort( $appointmentsByStatus['future'], function ( $a, $b ) {
            return $a->getDate() <=> $b->getDate();
        } );
        usort( $appointmentsByStatus['past'], function ( $a, $b ) {
            return $b->getDate() <=> $a->getDate();
        } );

        return $appointmentsByStatus;
    }

    /**
     * @return Appointment[]
     */
    public function getAppointments() : array
    {
        return $this->appointmentRepository->findAllOrderedByDate();
    }

    public function getCountAppointments() : int
    {
        return $this->appointmentRepository->countAppointments();
    }

    public function addOrUpdateAppointment( AppointmentData $appointmentData, Appointment $appointment = null ) : Appointment
    {
        $appointment = $appointment ?? new Appointment();
        $appointment->setClient( $appointmentData->client )
            ->setPrestation( $appointmentData->prestation )
            ->setDate( $appointmentData->date )
            ->setSubTotal( $appointmentData->prestation->getPrice() )
            ->setTotal( $appointmentData->prestation->getPrice() )
            ->setAccessToken( $this->tokenGeneratorService->generate() );

        if ( $appointmentData->autoConfirm ) {
            $appointment->setIsConfirmed( true );
        }

        if ( isset( $appointmentData->slotId ) ) {
            $slot = $this->getSlotById( $appointmentData->slotId, $appointmentData->date, $appointmentData->prestation );
            if ( $slot ) {
                $appointment->setStartTime( $slot->getStartTime() )
                    ->setEndTime( $slot->getEndTime() );
            }
        }

        $this->appointmentRepository->save( $appointment, true );
        return $appointment;
    }

    public function deleteAppointment( Appointment $appointment ) : void
    {
        $this->appointmentRepository->remove( $appointment, true );
    }

    public function updateAppointment( Appointment $appointment ) : void
    {
        $this->appointmentRepository->save( $appointment, true );
    }

    public function confirmAppointment( Appointment $appointment ) : void
    {
        $appointment->setIsConfirmed( true );
        $this->appointmentRepository->save( $appointment, true );

        $this->eventDispatcher->dispatch( new ConfirmedAppointmentEvent( $appointment ) );
    }

    public function cancelAppointment( Appointment $appointment ) : void
    {
        $appointment->setIsConfirmed( false );
        $this->appointmentRepository->save( $appointment, true );

        $this->eventDispatcher->dispatch( new CanceledAppointmentEvent( $appointment ) );
    }

    /**
     * @return Appointment[]
     */
    public function getReservedAppointments() : array
    {
        return $this->appointmentRepository->findReservedAppointments();
    }

    public function getAppointmentByAccessToken( string $accessToken )
    {
        return $this->appointmentRepository->findOneBy( ['accessToken' => $accessToken] );
    }

    public function getAppointmentById( ?int $getId )
    {
        return $this->appointmentRepository->findOneBy( ['id' => $getId] );
    }

    public function getUserAppointments( User $user )
    {
        return $this->appointmentRepository->findBy( ['client' => $user] );
    }

    public function getAppointmentsByDate( DateTime $date )
    {
        return $this->appointmentRepository->findBy( ['date' => $date] );
    }

    /**
     * Retourne les créneaux horaires disponibles pour une date et une prestation données
     * @param DateTimeInterface $date
     * @param Prestation $prestation
     * @return array
     * @throws Exception
     */
    public function getSlots( DateTimeInterface $date, Prestation $prestation ) : array
    {
        if ( $this->isHoliday( $date ) ) {
            return [];
        }

        $slots = $this->generateSlots( $date, $prestation );
        return $this->filterConflictingSlots( $slots, $date, $prestation );
    }

    /**
     * @throws Exception
     */
    public function getSlotsString( DateTimeInterface $date, Prestation $prestation ) : array
    {
        $slots = $this->getSlots( $date, $prestation );
        $slotsString = [];
        foreach ( $slots as $slot ) {
            $slotsString[] = $slot->getStartTime()->format( 'H:i' ) . ' - ' . $slot->getEndTime()->format( 'H:i' );
        }
        return $slotsString;
    }

    /**
     * Return true if the given date belongs to a holiday
     * @param DateTimeInterface $date
     * @return bool
     */
    private function isHoliday( DateTimeInterface $date ) : bool
    {
        $holidays = $this->holidayService->getAll();
        foreach ( $holidays as $holiday ) {
            if ( $date >= $holiday->getStartDate() && $date <= $holiday->getEndDate() ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Génère les créneaux horaires pour une date et une prestation données
     * @param DateTimeInterface $date
     * @param Prestation $prestation
     * @return array
     * @throws Exception
     */
    private function generateSlots( DateTimeInterface $date, Prestation $prestation ) : array
    {
        $slots = [];
        $start = clone $prestation->getStartTime();
        $start = new \DateTime( $date->format( 'Y-m-d' ) . ' ' . $start->format( 'H:i' ) );
        $end = $prestation->getEndTime();
        $end = new \DateTime( $date->format( 'Y-m-d' ) . ' ' . $end->format( 'H:i' ) );

        $duration = $prestation->getDuration();
        $interval = new \DateInterval( 'PT' . $duration->format( 'H' ) . 'H' . $duration->format( 'i' ) . 'M' );

        $currentDateTime = new \DateTime();

        while ( $start < $end ) {
            $endSlot = clone $start;
            $endSlot->add( $interval );

            if ( $endSlot > $end ) {
                break; // Ne pas créer de créneau qui dépasse l'heure de fin de la prestation
            }

            if ( $currentDateTime >= $start ) {
                $start->add( $interval );
                continue; // Ne pas créer de créneau pour les heures déjà passées
            }

            $slotTimeStart = clone $start;
            $slotTimeEnd = clone $endSlot;
            $slots[] = new Slot( $slotTimeStart, $slotTimeEnd );
//            $slots[$start->format( 'H:i' ) . ' - ' . $endSlot->format( 'H:i' )] = $start->format( 'H:i' ) . ' - ' . $endSlot->format( 'H:i' );
            $start->add( $interval );
        }

        return $slots;
    }

    /**
     * Supprime les créneaux qui entrent en conflit avec un rendez-vous donné
     * @param array $slots
     * @param Appointment $appointment
     * @param DateTimeInterface $date
     * @return array
     */
    private function removeConflictingSlots( array $slots, Appointment $appointment, DateTimeInterface $date ) : array
    {
        $appStart = $this->createDateTimeFromAppointment( $date, $appointment->getStartTime() );
        $appEnd = $this->createDateTimeFromAppointment( $date, $appointment->getEndTime() );

        foreach ( $slots as $slotId => $slot ) {
            if ( $this->isSlotConflicting( $slot, $appStart, $appEnd, $date ) ) {
                unset( $slots[$slotId] );
            }
        }
        $slots = array_values( $slots );
        return $slots;
    }

    /**
     * Checks if a given slot conflicts with the appointment time
     * @param string $slot
     * @param DateTime $appStart
     * @param DateTime $appEnd
     * @param DateTimeInterface $date
     * @return bool
     */
    private function isSlotConflicting( Slot $slot, DateTime $appStart, DateTime $appEnd, DateTimeInterface $date ) : bool
    {
//        [$slotStartStr, $slotEndStr] = explode( ' - ', $slot );
        $slotStartStr = $slot->getStartTime()->format( 'H:i' );
        $slotEndStr = $slot->getEndTime()->format( 'H:i' );
        $slotStart = new \DateTime( $date->format( 'Y-m-d' ) . ' ' . $slotStartStr );
        $slotEnd = new \DateTime( $date->format( 'Y-m-d' ) . ' ' . $slotEndStr );

        return ( $appStart <= $slotStart && $appEnd >= $slotEnd ) ||
            ( $appStart <= $slotStart && $appEnd > $slotStart ) ||
            ( $appStart < $slotEnd && $appEnd >= $slotEnd ) ||
            ( $appStart > $slotStart && $appEnd < $slotEnd );
    }

    /**
     * Creates a DateTime object for a given time based on the appointment date
     * @param DateTimeInterface $date
     * @param DateTimeInterface $time
     * @return DateTime
     */
    private function createDateTimeFromAppointment( DateTimeInterface $date, DateTimeInterface $time ) : DateTime
    {
        return new DateTime( $date->format( 'Y-m-d' ) . ' ' . $time->format( 'H:i' ) );
    }

    /**
     * Filtre les créneaux horaires en fonction des rendez-vous déjà pris
     * @param array $slots
     * @param DateTimeInterface $date
     * @param Prestation $prestation
     * @return array
     * @throws Exception
     */
    private function filterConflictingSlots( array $slots, DateTimeInterface $date, Prestation $prestation ) : array
    {
        $appointments = $this->appointmentRepository->findBy( ['date' => $date, 'status' => Appointment::STATUS_CONFIRMED] );
        foreach ( $appointments as $appointment ) {
            $slots = $this->removeConflictingSlots( $slots, $appointment, $date );
        }
        return $slots;
    }

    /**
     * Retourne les horaires de début et de fin d'un créneau horaire spécifié par son ID.
     * @param mixed $slotId
     * @param DateTime $date
     * @param Prestation $prestation
     * @return array|null
     * @throws Exception
     */
    public function getSlotById( mixed $slotId, DateTime $date, Prestation $prestation ) : ?Slot
    {
        $slots = $this->getSlots( $date, $prestation );

        if ( empty( $slots ) || !isset( $slots[$slotId] ) ) {
            return null;
        }

        return $slots[$slotId];
    }

    public function getAppointmentsPaginated( int $page = 1, int $limit = 10 )
    {
        return $this->appointmentRepository->findAppointmentPaginated( $page, $limit );
    }

    /**
     * Parse un créneau horaire pour extraire les heures de début et de fin.
     * @param Slot $slot
     * @return array
     */
    private function parseSlotTime( Slot $slot ) : array
    {
//        [$timeStart, $timeEnd] = explode( ' - ', $slot );
        $timeStart = $slot->getStartTime()->format( 'H:i' );
        $timeEnd = $slot->getEndTime()->format( 'H:i' );
        return [$timeStart, $timeEnd];
    }
}