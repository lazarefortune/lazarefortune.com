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

    public function searchUserByNameOrEmail( string $query )
    {
        return $this->createQueryBuilder( 'u' )
            ->andWhere( 'u.roles LIKE :role' )
            ->andWhere( 'u.fullname LIKE :query OR u.email LIKE :query' )
            ->orderBy( 'u.createdAt', 'DESC' )
            ->setParameter( 'role', '%ROLE_USER%' )
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

    public function countDailyUsersLast30Days(): array
    {
        $date = new \DateTime();
        $date->modify('-30 days');

        $result = $this->createQueryBuilder('u')
            ->select('u.createdAt')
            ->andWhere('u.roles NOT LIKE :role')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        // Initialise un tableau avec les 30 derniers jours
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $day = (clone $date)->modify("+$i days")->format('Y-m-d');
            $data[$day] = 0;
        }

        // Compte les utilisateurs par jour
        foreach ($result as $item) {
            $day = $item['createdAt']->format('Y-m-d');
            if (isset($data[$day])) {
                $data[$day]++;
            }
        }

        // Retourne les données sous forme de tableau d'objets
        return array_map(function ($day, $users) {
            return ['day' => $day, 'users' => $users];
        }, array_keys($data), $data);
    }

    public function countMonthlyUsersLast24Months(): array
    {
        $date = new \DateTime();
        $date->modify('-24 months');

        $result = $this->createQueryBuilder('u')
            ->select('u.createdAt')
            ->andWhere('u.roles NOT LIKE :role')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        // Initialise un tableau avec les 24 derniers mois
        $data = [];
        for ($i = 0; $i <= 24; $i++) {
            $month = (clone $date)->modify("+$i months")->format('Y-m');
            $data[$month] = 0;
        }

        // Compte les utilisateurs par mois
        foreach ($result as $item) {
            $month = $item['createdAt']->format('Y-m');
            if (isset($data[$month])) {
                $data[$month]++;
            }
        }

        // Retourne les données sous forme de tableau d'objets
        return array_map(function ($month, $users) {
            return ['month' => $month, 'users' => $users];
        }, array_keys($data), $data);
    }


    /**
     * Trouve un utilisateur par email ou crée un nouvel utilisateur avec l'ID Google.
     */
    public function findOrCreateUserFromGoogle(string $email, string $googleId): User
    {
        $user = $this->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setGoogleId($googleId);
            $user->setRoles(['ROLE_USER']); // Ajouter des rôles par défaut, si nécessaire

            $this->_em->persist($user);
            $this->_em->flush();
        }

        return $user;
    }

    /**
     * Search User by OAuth service and email
     */
    public function findForOauth(string $service, ?string $serviceId, ?string $email): ?User
    {
        if (null === $serviceId || null === $email) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->orWhere("u.{$service}Id = :serviceId")
            ->setMaxResults(1)
            ->setParameter('email', $email)
            ->setParameter('serviceId', $serviceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les utilisateurs dont l'abonnement premium expire dans les 3 prochains jours.
     */
    public function findUsersWithPremiumEndingSoon( int $days = 3 ): array
    {
        // Date de début : maintenant
        $now = new \DateTime();

        // Date de fin : maintenant + $days jours (fin de journée)
        $endDate = (new \DateTime("+{$days} days"))->setTime(23, 59, 59);

        return $this->createQueryBuilder('u')
            ->where('u.premiumEnd >= :now')
            ->andWhere('u.premiumEnd <= :endDate')
            ->setParameter('now', $now)
            ->setParameter('endDate', $endDate)
            ->orderBy('u.premiumEnd', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
