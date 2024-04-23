<?php

namespace App\Domain\Client\Service;

use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\EmailConfirmationRequestedEvent;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Client\Event\DeleteClientEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService
{

    public function __construct(
        private readonly UserRepository           $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AppointmentRepository    $appointmentRepository,
    )
    {
    }

    /**
     * @return User[]
     */
    public function getClients() : array
    {
        return $this->userRepository->findByRole( 'ROLE_CLIENT' );
    }

    public function getCountClients() : int
    {
        return $this->userRepository->countClients();
    }

    public function getClientsByMonth() : array
    {
        return $this->userRepository->countClientsByMonth();
    }

    /**
     * @throws \Exception
     */
    public function getClient( int $id ) : User
    {
        $user = $this->userRepository->findOneBy( ['id' => $id] );

        if ( !$user ) {
            throw new \Exception( 'Aucun client trouvé' );
        }

        return $user;
    }

    public function search( string $query )
    {
        return $this->userRepository->searchClientByNameAndEmail( $query );
    }

    public function getClientAppointments( User $client, int $limit = null )
    {
        return $this->appointmentRepository->createQueryBuilder( 'b' )
            ->where( 'b.client = :client' )
            ->setParameter( 'client', $client )
            ->orderBy( 'b.date', 'DESC' )
            ->addOrderBy( 'b.startTime', 'DESC' )
            ->setMaxResults( $limit )
            ->getQuery()
            ->getResult();
    }

    public function sendEmailAction( User $client, string $action ) : void
    {
        // TODO: check if action is available for client before sending and refactor actions

        switch ( $action ) {
            case 'last_invoice':
//                $this->sendLastInvoice( $client );
                throw new \Exception( 'Non disponible pour le moment' );
                break;
            case 'payment_reminder':
//                $this->sendPaymentReminder( $client );
                throw new \Exception( 'Non disponible pour le moment' );
                break;
            case 'appointment_reminder':
//                $this->sendAppointmentReminder( $client );
                throw new \Exception( 'Non disponible pour le moment' );
                break;
            case 'password_reset':
//                $this->sendPasswordReset( $client );
                throw new \Exception( 'Non disponible pour le moment' );
                break;
            case 'account_confirmation':
                $this->sendAccountConfirmation( $client );
                break;
        }
    }
}