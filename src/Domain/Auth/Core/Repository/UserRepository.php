<?php

namespace App\Domain\Auth\Core\Repository;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Event\Delete\PreviousUserDeleteRequestEvent;
use App\Domain\Auth\Core\Event\Delete\UserRequestDeleteSuccessEvent;
use App\Domain\Auth\Core\Event\Unverified\DeleteUnverifiedUserSuccessEvent;
use App\Domain\Auth\Core\Event\Unverified\PreviousDeleteUnverifiedUserEvent;
use App\Domain\Auth\Core\Service\DeleteAccountService;
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

    public function getQueryUsersWithoutRoles( array $roles ) : \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder( 'u' )
            ->where('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%"' . implode('"%" AND u.roles NOT LIKE "%', $roles) . '"%')
            ->orderBy('u.email', 'ASC');
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
        $currentDate = new \DateTime();

        // Date de suppression des utilisateurs non vérifiés
        $deletionDate = (clone $currentDate)->modify('-' . User::DAYS_BEFORE_DELETE_UNVERIFIED_USER . ' days');

        // Récupère les utilisateurs non vérifiés qui doivent être supprimés
        $usersToDelete = $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.createdAt < :deletionDate')
            ->andWhere('u.isVerified = false')
            ->setParameter('deletionDate', $deletionDate)
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();

        // Envoie des notifications de suppression imminente (période d'avertissement)
        $warningDate = (clone $currentDate)->modify('-' . User::DAYS_FOR_PREVENT_DELETE_UNVERIFIED_USER . ' days');

        $usersToWarn = $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.createdAt < :warningDate')
            ->andWhere('u.createdAt >= :deletionDate')
            ->andWhere('u.isVerified = false')
            ->setParameter('warningDate', $warningDate)
            ->setParameter('deletionDate', $deletionDate)
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();

        foreach ($usersToWarn as $user) {
            $this->dispatcher->dispatch(new PreviousDeleteUnverifiedUserEvent($user));
        }

        // Supprime les utilisateurs non vérifiés dont la date de suppression est atteinte
        $count = 0;
        foreach ($usersToDelete as $user) {
            $this->dispatcher->dispatch(new DeleteUnverifiedUserSuccessEvent($user));
            $this->deleteAccountService->deleteAccount($user);
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
        $currentDate = new \DateTime();

        // Date de suppression des utilisateurs ayant demandé la suppression
        $deletionDate = $currentDate;

        // Date d'avertissement (n jours avant la suppression)
        $warningDate = (clone $currentDate)->modify('+' . User::DAYS_FOR_PREVENT_DELETE_USER . ' days');

        // Récupère les utilisateurs qui doivent être avertis de la suppression imminente
        $usersToWarn = $this->createQueryBuilder('u')
            ->andWhere('u.deletedAt IS NOT NULL')
            ->andWhere('u.deletedAt <= :warningDate')
            ->andWhere('u.deletedAt > :deletionDate')
            ->andWhere('u.roles NOT LIKE :role')
            ->setParameter('warningDate', $warningDate)
            ->setParameter('deletionDate', $deletionDate)
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->getResult();

        foreach ($usersToWarn as $user) {
            $this->dispatcher->dispatch(new PreviousUserDeleteRequestEvent($user));
        }

        // Récupère les utilisateurs qui doivent être supprimés (date de suppression atteinte)
        $usersToDelete = $this->createQueryBuilder('u')
            ->andWhere('u.deletedAt IS NOT NULL')
            ->andWhere('u.deletedAt <= :deletionDate')
            ->andWhere('u.roles NOT LIKE :role')
            ->setParameter('deletionDate', $deletionDate)
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->getResult();

        // Supprime les utilisateurs dont la date de suppression est atteinte
        $count = 0;
        foreach ($usersToDelete as $user) {
            $this->dispatcher->dispatch(new UserRequestDeleteSuccessEvent($user));
            $this->deleteAccountService->deleteAccount($user);
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

    public function countMonthlyUsersLastYearFormatted(): array
    {
        $date = new \DateTime();
        $date->modify('-1 year');

        // Tableau des mois avec initialisation à 0 utilisateur
        $formattedData = [
            ['month' => 'Janvier', 'users' => 0],
            ['month' => 'Février', 'users' => 0],
            ['month' => 'Mars', 'users' => 0],
            ['month' => 'Avril', 'users' => 0],
            ['month' => 'Mai', 'users' => 0],
            ['month' => 'Juin', 'users' => 0],
            ['month' => 'Juillet', 'users' => 0],
            ['month' => 'Août', 'users' => 0],
            ['month' => 'Septembre', 'users' => 0],
            ['month' => 'Octobre', 'users' => 0],
            ['month' => 'Novembre', 'users' => 0],
            ['month' => 'Décembre', 'users' => 0],
        ];

        $result = $this->createQueryBuilder('u')
            ->select('COUNT(u) as total, MONTH(u.createdAt) as month')
            ->andWhere('u.roles NOT LIKE :role')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->setParameter('date', $date)
            ->groupBy('month')
            ->getQuery()
            ->getResult();

        // Intègre les données réelles dans le tableau formaté
        foreach ($result as $item) {
            $formattedData[$item['month'] - 1]['users'] = (int)$item['total']; // -1 pour correspondre à l'indice du mois
        }

        return $formattedData;
    }

}
