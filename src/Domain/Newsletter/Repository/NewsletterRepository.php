<?php

namespace App\Domain\Newsletter\Repository;

use App\Domain\Course\Entity\Course;
use App\Domain\Newsletter\Entity\Newsletter;
use App\Domain\Newsletter\Enum\NewsletterStatus;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Course>
 */
class NewsletterRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    /**
     * Récupère les newsletters prêtes à être envoyées
     */
    public function findReadyToSend()
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('n')
            ->where('n.status = :status')
            ->andWhere('n.isDraft = false')
            ->andWhere('n.sendAt <= :now')
            ->setParameter('status', NewsletterStatus::PENDING)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }


}
