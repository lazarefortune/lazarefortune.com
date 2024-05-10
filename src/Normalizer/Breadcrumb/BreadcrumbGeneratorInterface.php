<?php

namespace App\Normalizer\Breadcrumb;

interface BreadcrumbGeneratorInterface
{
    /**
     * @param object $entity
     */
    public function generate( object $entity): array;

    public function support(object $object): bool;
}