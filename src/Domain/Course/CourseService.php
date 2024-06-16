<?php

namespace App\Domain\Course;

use App\Domain\Course\Repository\CourseRepository;
use Doctrine\ORM\QueryBuilder;

class CourseService
{
    public function __construct(
        private readonly CourseRepository $courseRepository
    )
    {
    }


    public function getCourseList(): QueryBuilder
    {
        return $this->courseRepository->queryAll();
    }

    public function getNbCoursesOnline() : int {
        return $this->courseRepository->getNbCoursesOnline();
    }

    public function getCourseBySlug( $slug )
    {
        return $this->courseRepository->queryBuSlug($slug);
    }


}