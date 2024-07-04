<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Profile\Event\UserUnverifiedRemoveEvent;
use App\Domain\Profile\Service\DeleteAccountService;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find( $id, $lockMode = null, $lockVersion = null )
 * @method User|null findOneBy( array $criteria, array $orderBy = null )
 * @method User[]    findAll()
 * @method User[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, CleanableRepositoryInterface
{
    public function __construct(
        ManagerRegistry                           $registry,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DeleteAccountService     $deleteAccountService
    )
    {
        parent::__construct( $registry, User::class );
    }

    public function save( User $entity, bool $flush = false ) : void
    {
        $this->getEntityManager()->persist( $entity );

        if ( $flush ) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove( User $entity, bool $flush = false ) : void
    {
        $this->getEntityManager()->remove( $entity );

        if ( $flush ) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword( PasswordAuthenticatedUserInterface $user, string $newHashedPassword ) : void
    {
        if ( !$user instanceof User ) {
            throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', \get_class( $user ) ) );
        }

        $user->setPassword( $newHashedPassword );

        $this->save( $user, true );
    }

    /**
     * @return User[]
     */
    public function findByRole( string $string ) : array
    {
        return $this->createQueryBuilder( 'u' )
            ->andWhere( 'u.roles LIKE :role' )
            ->setParameter( 'role', '%' . $string . '%' )
            ->getQuery()
            ->getResult();
    }

    public function searchClientByNameAndEmail( string $query )
    {
        return $this->createQueryBuilder( 'u' )
            ->andWhere( 'u.roles LIKE :role' )
            ->andWhere( 'u.fullname LIKE :query OR u.email LIKE :query' )
            ->orderBy( 'u.createdAt', 'DESC' )
            ->setParameter( 'role', '%ROLE_CLIENT%' )
            ->setParameter( 'query', '%' . $query . '%' )
            ->getQuery()
            ->getResult();
    }

    public function removeAllUnverifiedAccount() : int
    {
        $date = new \DateTime();
        $date->modify( '-' . User::DAYS_BEFORE_DELETE_UNVERIFIED_USER . ' days' );

        // get all user with unverified email and created before few days
        $users = $this->createQueryBuilder( 'u' )
            ->andWhere( 'u.roles LIKE :role' )
            ->andWhere( 'u.createdAt < :date' )
            ->andWhere( 'u.isVerified = false' )
            ->setParameter( 'date', $date )
            ->setParameter( 'role', '%ROLE_USER%' )
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ( $users as $user ) {
            $this->deleteAccountService->deleteAccount( $user );
            $this->dispatcher->dispatch( new UserUnverifiedRemoveEvent( $user ) );
            $count++;
        }

        return $count;
    }

    public function clean() : int
    {
        return $this->removeAllUnverifiedAccount();
    }

    public function cleanUsersDeleted() : int
    {
        $date = new \DateTime();

        $users = $this->createQueryBuilder( 'u' )
            ->andWhere( 'u.roles NOT LIKE :role' )
            ->andWhere( 'u.deletedAt IS NOT NULL' )
            ->andWhere( 'u.deletedAt <= :date' )
            ->setParameter( 'date', $date )
            ->setParameter( 'role', '%ROLE_SUPER_ADMIN%' )
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ( $users as $user ) {
            $this->deleteAccountService->deleteAccount( $user );
            // TODO: Dispatch event
            $count++;
        }

        return $count;
    }

    public function countUsers() : int
    {
        return $this->createQueryBuilder( 'u' )
            ->select( 'COUNT(u)' )
            ->andWhere( 'u.roles NOT LIKE :role' )
            ->setParameter( 'role', '%ROLE_SUPER_ADMIN%' )
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countMonthlyUsersLastYear() : array
    {
        $date = new \DateTime();
        $date->modify( '-1 year' );

        $result = $this->createQueryBuilder( 'u' )
            ->select( 'COUNT(u) as total, MONTH(u.createdAt) as month' )
            ->andWhere( 'u.roles NOT LIKE :role' )
            ->andWhere( 'u.createdAt >= :date' )
            ->setParameter( 'role', '%ROLE_SUPER_ADMIN%' )
            ->setParameter( 'date', $date )
            ->groupBy( 'month' )
            ->getQuery()
            ->getResult();

        $data = array_fill( 1, 12, 0 );
        foreach ( $result as $item ) {
            $data[$item['month']] = (int)$item['total'];
        }

        return $data;
    }
}
