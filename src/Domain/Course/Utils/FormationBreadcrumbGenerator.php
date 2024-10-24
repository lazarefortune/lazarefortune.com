<?php

namespace App\Domain\Course\Utils;

use App\Domain\Course\Entity\Formation;
use App\Normalizer\Breadcrumb\BreadcrumbGeneratorInterface;
use App\Normalizer\Breadcrumb\BreadcrumbItem;

class FormationBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{

    public function generate( $formation ) : array
    {
        $items = [];
        $items[] = new BreadcrumbItem('Playlists', ['app_formation_index']);
        $categories = [];
        foreach ($formation->getMainTechnologies() as $technology) {
            $categories[] = new BreadcrumbItem(
                (string) $technology->getName(),
                ['app_technology_show', ['slug' => $technology->getSlug()]]
            );
        }
        if (count($categories) > 0) {
            $items[] = $categories;
        }

        return $items;
    }

    public function support( object $object ) : bool
    {
        return $object instanceof Formation;
    }
}