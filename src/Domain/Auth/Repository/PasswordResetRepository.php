<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\PasswordReset;
use App\Domain\Auth\Entity\User;
use App\Infrastructure\Orm\AbstractRepository;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<PasswordReset>
 */
class PasswordResetRepository extends AbstractRepository implements CleanableRepositoryInterface
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, PasswordReset::class );
    }

    public function findLatestValidPasswordReset( User $user )
    {
        $date = new \DateTime();
        $date->modify( sprintf( '-%d seconds', PasswordReset::TOKEN_EXPIRATION_TIME ) );

        return $this->createQueryBuilder( 'v' )
            ->where( 'v.author = :user' )
            ->andWhere( 'v.createdAt > :date' )
            ->setParameter( 'user', $user )
            ->setParameter( 'date', $date )
            ->setMaxResults( 1 )
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Delete expired password resets and return the number of deleted rows
     * @return int
     */
    public function deleteExpiredPasswordResets() : int
    {
        $date = new \DateTime();
        $date->modify( sprintf( '-%d seconds', PasswordReset::TOKEN_EXPIRATION_TIME ) );

        return $this->createQueryBuilder( 'v' )
            ->delete()
            ->where( 'v.createdAt < :date' )
            ->setParameter( 'date', $date )
            ->getQuery()
            ->execute();
    }

    public function clean() : int
    {
        return $this->deleteExpiredPasswordResets();
    }
}