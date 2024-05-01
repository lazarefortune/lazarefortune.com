<?php

namespace App\Domain\Attachment\Repository;

use App\Domain\Application\Entity\Content;
use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Course\Entity\Course;
use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Attachment>
 */
class AttachmentRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attachment::class);
    }

    public function findYearsMonths(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('EXTRACT(MONTH FROM a.createdAt) as month, EXTRACT(YEAR FROM a.createdAt) as year, COUNT(a.id) as count')
            ->groupBy('month', 'year')
            ->orderBy('month', 'DESC')
            ->orderBy('year', 'DESC')
            ->getQuery()
            ->getResult();

//        return like this [
//            ['id' => '2024', 'name' => '2024', 'parent' => null],
//            ['id' => '2024/04', 'name' => '04', 'parent' => '2024'],
//            ['id' => '2024/03', 'name' => '03', 'parent' => '2024'],
//        ];

        $result = [];
        foreach ($rows as $row) {
            $year = (int) $row['year'];
            $month = (int) $row['month'];

            if (!in_array(['id' => (string) $year, 'name' => (string) $year, 'parent' => null], $result)) {
                $result[] = [
                    'id' => (string) $year,
                    'name' => (string) $year,
                    'parent' => null,
                ];
            }

            $result[] = [
                'id' => "$year/$month",
                'name' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'parent' => (string) $year,
            ];
        }


        return $result;
    }

//    private function mapFolderDetails($folder): array
//    {
//        $relativePath = $this->toRelativePath($folder->getRealPath());
//        return [
//            'id' => trim($relativePath, '/'),
//            'name' => $folder->getFilename(),
//            'parent' => dirname($relativePath) === '.' ? null : trim(dirname($relativePath), '/')
//        ];
//    }

    /**
     * @return array<Attachment>
     */
    public function findForPath(string $path): array
    {
        $parts = explode('/', $path);

        if (count($parts) !== 2) {
            return [];
        }

        $start = new \DateTimeImmutable("{$parts[0]}-{$parts[1]}-01");
        $end = $start->modify('+1 month -1 second');

        return $this->createQueryBuilder('a')
            ->where('a.createdAt BETWEEN :start AND :end')
            ->setParameters([
                'start' => $start,
                'end' => $end,
            ])
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<Attachment>
     */
    public function findLatest()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<Attachment>
     */
    public function search(string $q)
    {
        return $this->createQueryBuilder('a')
            ->where('a.fileName LIKE :search')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(25)
            ->setParameter('search', "%$q%")
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les fichiers non rattachés à un contenu
     * @return array<Attachment>
     */
    public function orphaned(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->orderBy('a.createdAt', 'DESC')
            ->leftJoin(
                Content::class,
                'c',
                Join::WITH,
                'c.image = a.id'
            )
            ->leftJoin(
                Course::class,
                'course',
                Join::WITH,
                'course.youtubeThumbnail = a.id'
            )
            ->where('c.id IS NULL AND course.id IS NULL')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }
}