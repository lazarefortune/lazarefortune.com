<?php

namespace App\Domain\Auth\Registration\Repository;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Registration\Entity\EmailVerification;
use App\Infrastructure\Orm\AbstractRepository;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends AbstractRepository<EmailVerification>
 */
class EmailVerificationRepository extends AbstractRepository implements CleanableRepositoryInterface
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, EmailVerification::class );
    }

    public function findEmailVerification( User $user )
    {
        return $this->createQueryBuilder( 'v' )
            ->where( 'v.author = :user' )
            ->setParameter( 'user', $user )
            ->setMaxResults( 1 )
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestValidEmailVerification( User $user )
    {
        $date = new \DateTime();
        $date->modify( sprintf( '-%d seconds', EmailVerification::TOKEN_EXPIRATION_TIME ) );

        return $this->createQueryBuilder( 'v' )
            ->where( 'v.author = :user' )
            ->andWhere( 'v.createdAt > :date' )
            ->setParameter( 'user', $user )
            ->setParameter( 'date', $date )
            ->setMaxResults( 1 )
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEmailVerificationByEmail( string $email )
    {
        return $this->createQueryBuilder( 'v' )
            ->where( 'v.email = :email' )
            ->setParameter( 'email', $email )
            ->setMaxResults( 1 )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Delete expired email verifications and return the number of deleted rows
     * @return int
     */
    public function deleteExpiredEmailVerifications() : int
    {
        $date = new \DateTime();
        $date->modify( sprintf( '-%d seconds', EmailVerification::TOKEN_EXPIRATION_TIME ) );

        return $this->createQueryBuilder( 'v' )
            ->delete()
            ->where( 'v.createdAt < :date' )
            ->setParameter( 'date', $date )
            ->getQuery()
            ->execute();

    }

    public function clean() : int
    {
        return $this->deleteExpiredEmailVerifications();
    }
}
