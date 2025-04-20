<?php

namespace App\Domain\Course;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Repository\CourseRepository;
use Doctrine\ORM\QueryBuilder;

class CourseService
{
    public function __construct(
        private readonly CourseRepository $courseRepository
    )
    {
    }


    public function getCourseList( $isPremium = false ): QueryBuilder
    {
        return $this->courseRepository->queryAll( $isPremium );
    }

    public function countOnlineCourses( User $user = null ) : int {
        return $this->courseRepository->countOnlineCourses( $user );
    }

    public function getLastCourses( $limit = null ) : array
    {
        return $this->courseRepository->getRecentVideos( $limit );
    }


}