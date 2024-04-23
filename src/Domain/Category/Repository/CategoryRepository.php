<?php

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Category>
 */
class CategoryRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Category::class );
    }
}
