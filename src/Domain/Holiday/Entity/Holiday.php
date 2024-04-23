<?php

namespace App\Domain\Holiday\Entity;

use App\Domain\Holiday\Repository\HolidayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: HolidayRepository::class )]
class Holiday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255, unique: true )]
    private ?string $title = null;

    #[ORM\Column( type: Types::DATE_MUTABLE )]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column( type: Types::DATE_MUTABLE )]
    private ?\DateTimeInterface $endDate = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setTitle( string $title ) : self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate() : \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate( \DateTimeInterface $startDate ) : self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate() : \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate( \DateTimeInterface $endDate ) : self
    {
        $this->endDate = $endDate;

        return $this;
    }
}
