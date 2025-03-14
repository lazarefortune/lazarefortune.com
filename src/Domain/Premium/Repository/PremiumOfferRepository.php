<?php

namespace App\Domain\Premium\Repository;

use App\Domain\Premium\Entity\Plan;
use App\Domain\Premium\Entity\PremiumOffer;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Plan>
 */
class PremiumOfferRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PremiumOffer::class);
    }
}
