<?php

namespace App\Domain\Contact\Repository;

use App\Domain\Contact\Entity\Contact;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Contact>
 */
class ContactRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Contact::class );
    }
}
