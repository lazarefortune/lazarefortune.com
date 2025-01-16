<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Entity\Technology;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Technology>
 */
class TechnologyRepository extends AbstractRepository
{
    public function __construct( ManagerRegistry $registry)
    {
        parent::__construct( $registry, Technology::class );
    }

//    public function findByType(): array
//    {
//        $types = [
//            'Backend' => ['php', 'laravel', 'symfony', 'nodejs'],
//            'Frontend' => ['html', 'css', 'javascript', 'react'],
//            'Outils' => ['git', 'docker', 'linux'],
//        ];
//        $slugs = [];
//        foreach ($types as $type) {
//            $slugs = array_merge($slugs, $type);
//        }
//
//        /** @var Technology[] $technologies */
//        $technologies = $this->findBy(['slug' => $slugs]);
//        if (empty($technologies)) {
//            return [];
//        }
//
//        $technologies = collect($technologies)->keyBy(fn (Technology $t) => $t->getSlug() ?? '')->toArray();
//        foreach ($types as $k => $v) {
//            $types[$k] = collect($v)->map(fn (string $slug) => $technologies[$slug] ?? null)->filter()->toArray();
//        }
//
//        return $types;
//    }

    /**
     * Trouve une technologie par rapport à son nom (non sensible à la casse).
     */
    public function findByName(string $name): ?Technology
    {
        return $this->createQueryBuilder('t')
            ->where('LOWER(t.name) = :technology')
            ->setMaxResults(1)
            ->setParameter('technology', strtolower($name))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByNames(array $names): array
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.name) IN (:name)')
            ->setParameter('name', array_map(fn (string $name) => strtolower($name), $names))
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les technologies qui commence par le mot.
     */
    public function searchByName(string $q): array
    {
        return $this->createQueryBuilder('t')
            ->where('LOWER(t.name) LIKE :q')
            ->orderBy('LENGTH(t.name)', 'ASC')
            ->setMaxResults(3)
            ->setParameter('q', strtolower($q).'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les technologies avec le nombre de courses et de formations associées.
     * @return array
     */
    public function findAllWithContentCount(): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t.id', 't.name', 't.slug', 't.image', 'COUNT(DISTINCT c.id) AS courseCount', 'COUNT(DISTINCT f.id) AS formationCount')
            ->leftJoin('t.usages', 'u') // Jointure avec TechnologyUsage
            ->leftJoin('u.content', 'c', 'WITH', 'c INSTANCE OF App\Domain\Course\Entity\Course') // Filtre pour les cours
            ->leftJoin('u.content', 'f', 'WITH', 'f INSTANCE OF App\Domain\Course\Entity\Formation') // Filtre pour les formations
            ->groupBy('t.id')
            ->getQuery();

        return $query->getResult();
    }

}