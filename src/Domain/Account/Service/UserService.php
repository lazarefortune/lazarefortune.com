<?php

namespace App\Domain\Account\Service;

use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepository;

class UserService
{

    public function __construct(
        private readonly UserRepository           $userRepository,
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

    public function getNbUsers() : int
    {
        return $this->userRepository->countUsers();
    }

    public function getMonthlyUsersLastYear() : array
    {
        return $this->userRepository->countMonthlyUsersLastYear();
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
}