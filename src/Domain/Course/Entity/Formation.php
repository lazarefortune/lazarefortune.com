<?php

namespace App\Domain\Course\Entity;

use App\Domain\Application\Entity\Content;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Course\Repository\FormationRepository;

#[ORM\Entity( repositoryClass: FormationRepository::class )]
class Formation extends Content
{
    use ChapterableTrait;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $short = null;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $youtubePlaylist = null;

    #[ORM\OneToMany( mappedBy: 'formation', targetEntity: Course::class )]
    private Collection $courses;

    #[ORM\ManyToOne( targetEntity: Formation::class )]
    #[ORM\JoinColumn( onDelete: 'SET NULL' )]
    private ?Formation $deprecatedBy = null;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        parent::__construct();
    }

    public function getShort() : ?string
    {
        return $this->short;
    }

    public function setShort( ?string $short ) : self
    {
        $this->short = $short;

        return $this;
    }

    public function getYoutubePlaylist() : ?string
    {
        return $this->youtubePlaylist;
    }

    public function setYoutubePlaylist( ?string $youtubePlaylist ) : self
    {
        $this->youtubePlaylist = $youtubePlaylist;

        return $this;
    }

    public function getCourses() : Collection
    {
        return $this->courses;
    }

    public function addCourse( Course $course ) : self
    {
        if ( !$this->courses->contains( $course ) ) {
            $this->courses[] = $course;
            $course->setFormation( $this );
        }

        return $this;
    }

    public function removeCourse( Course $course ) : self
    {
        if ( $this->courses->contains( $course ) ) {
            $this->courses->removeElement( $course );
            // set the owning side to null (unless already changed)
            if ( $course->getFormation() === $this ) {
                $course->setFormation( null );
            }
        }

        return $this;
    }

    public function getCoursesById() : array
    {
        $courses = $this->getCourses();
        $coursesById = [];
        foreach ( $courses as $course ) {
            $coursesById[$course->getId()] = $course;
        }

        return $coursesById;
    }

    public function getDeprecatedBy() : ?Formation
    {
        return $this->deprecatedBy;
    }

    public function setDeprecatedBy( ?Formation $deprecatedBy ) : self
    {
        $this->deprecatedBy = $deprecatedBy;

        return $this;
    }

    public function getDuration() : int
    {
        return array_reduce( $this->courses->toArray(), function ( int $acc, Course $item ) {
            $acc += $item->getDuration();

            return $acc;
        }, 0 );
    }

    public function getNextCourseId(?int $id): ?int
    {
        if (null === $id) {
            return null;
        }
        $ids = array_reduce($this->getRawChapters(), fn ($acc, $chapter) => array_merge($acc, $chapter['modules']), []);
        $index = array_search($id, $ids);
        if (false === $index) {
            return null;
        }

        return $ids[(int) $index + 1] ?? null;
    }
}