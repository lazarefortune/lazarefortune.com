<?php

namespace App\Domain\Newsletter\Repository;

use App\Domain\Course\Entity\Course;
use App\Domain\Newsletter\Entity\Newsletter;
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
}
