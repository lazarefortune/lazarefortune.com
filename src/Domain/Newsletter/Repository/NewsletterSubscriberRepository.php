<?php

namespace App\Domain\Newsletter\Repository;

use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterSubscriberRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscriber::class);
    }
}
