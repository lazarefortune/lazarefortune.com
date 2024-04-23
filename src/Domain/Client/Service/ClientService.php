<?php

namespace App\Domain\Client\Service;

use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\EmailConfirmationRequestedEvent;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Client\Event\DeleteClientEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ClientService
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

    public function addNewClient( mixed $getData ) : void
    {
        $user = $this->userRepository->findOneBy( ['email' => $getData->getEmail()] );


        if ( $user ) {
            throw new \Exception( 'Un utilisateur avec cet email existe déjà' );
        }

        $user = $getData;
        $user->setRoles( ['ROLE_CLIENT'] );
        $user->setPassword( '' );
        $user->setCgu( false );
        $this->userRepository->save( $user, true );

        $registrationEvent = new UserRegistrationCompletedEvent( $user );
        $this->eventDispatcher->dispatch( $registrationEvent, UserRegistrationCompletedEvent::NAME );
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

    public function updateClient( User $client ) : void
    {
        $this->userRepository->save( $client, true );
    }

    public function deleteClient( User $client ) : void
    {
        $this->userRepository->remove( $client, true );
        $deleteClientEvent = new DeleteClientEvent( $client );
        $this->eventDispatcher->dispatch( $deleteClientEvent, DeleteClientEvent::NAME );
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

    private function sendAccountConfirmation( User $client )
    {
        $emailConfirmationRequestedEvent = new EmailConfirmationRequestedEvent( $client );
        $this->eventDispatcher->dispatch( $emailConfirmationRequestedEvent, EmailConfirmationRequestedEvent::NAME );
    }
}