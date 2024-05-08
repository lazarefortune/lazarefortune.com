<?php

namespace App\Domain\Course\EventListener;


use App\Domain\Course\Entity\Course;
use App\Helper\PathHelper;
use App\Infrastructure\Storage\VideoMetaReader;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CourseListener
{

    public function __construct(
        private readonly string $videosPath, private readonly VideoMetaReader $metaReader
    )
    {
    }

    public function preUpdate(PreUpdateEventArgs $args): void {
        $entity = $args->getObject();
        if ($entity instanceof Course) {
            if ($args->hasChangedField('videoPath')) {
                $this->updateDuration($entity);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $args): void {
        $entity = $args->getObject();
        if ($entity instanceof Course && !empty($entity->getVideoPath())) {
            $this->updateDuration($entity);
        }
    }

    private function updateDuration(Course $course): void
    {
        if (!empty($course->getVideoPath())) {
            $video = PathHelper::join($this->videosPath, $course->getVideoPath());
            $duration = $this->metaReader->getDuration($video);
            $course->setDuration($duration);
        }else{
            $course->setDuration(0);
        }
    }
}