<?php

namespace App\Infrastructure\Orm;

/**
 * Interface qui permet de nettoyer les entités
 */
interface CleanableRepositoryInterface
{
    public function clean(): int;
}