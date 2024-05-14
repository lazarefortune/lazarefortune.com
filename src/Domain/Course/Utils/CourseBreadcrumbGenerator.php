<?php

namespace App\Domain\Course\Utils;

use App\Domain\Course\Entity\Course;
use App\Normalizer\Breadcrumb\BreadcrumbGeneratorInterface;
use App\Normalizer\Breadcrumb\BreadcrumbItem;

class CourseBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param object $entity
     * @return array
     */
    public function generate( object $entity): array
    {
        $items = [];
        $items[] = new BreadcrumbItem('Tutoriels', ['app_course_index']);
        $categories = [];
        foreach ( $entity->getMainTechnologies() as $technology) {
            $categories[] = new BreadcrumbItem(
                (string) $technology->getName(),
                ['app_technology_show', ['slug' => $technology->getSlug()]]
            );
        }
        if (count($categories) > 0) {
            $items[] = $categories;
        }
        if ($formation = $entity->getFormation()) {
            $items[] = new BreadcrumbItem(
                (string) $formation->getTitle(),
                ['app_formation_show', ['slug' => $formation->getSlug()]]
            );
        }

        $items[] = new BreadcrumbItem((string) $entity->getTitle(), ['app_course_show', [
            'id' => $entity->getId(),
            'slug' => $entity->getSlug(),
        ]]);

        return $items;
    }

    public function support(object $object): bool
    {
        return $object instanceof Course;
    }
}