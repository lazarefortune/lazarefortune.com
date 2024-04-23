<?php

namespace App\Domain\Tag\Repository;

use App\Domain\Tag\Entity\Tag;
use App\Infrastructure\Orm\AbstractRepository;
use App\Infrastructure\Orm\CleanableRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends AbstractRepository<Tag>
 */
class TagRepository extends AbstractRepository implements CleanableRepositoryInterface
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Tag::class );

    }

    /**
     * Delete all unused tags and return the number of deleted rows
     * @return int
     */
    public function deleteAllUnusedTags() : int
    {
        return $this->createQueryBuilder( 't' )
            ->delete()
            ->where( 't.prestations IS EMPTY' )
            ->getQuery()
            ->execute();
    }


    public function clean() : int
    {
        return $this->deleteAllUnusedTags();
    }
}
