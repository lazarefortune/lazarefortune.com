<?php

namespace App\Domain\Category\Entity;

use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Prestation\Entity\Prestation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: CategoryRepository::class )]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255 )]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $description = null;

    #[ORM\OneToMany( mappedBy: 'categoryPrestation', targetEntity: Prestation::class )]
    private Collection $prestations;

    public function __construct()
    {
        $this->prestations = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName( string $name ) : self
    {
        $this->name = $name;

        return $this;
    }


    public function isIsActive() : ?bool
    {
        return $this->isActive;
    }

    public function setIsActive( bool $isActive ) : self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription( ?string $description ) : self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Prestation>
     */
    public function getPrestations() : Collection
    {
        return $this->prestations;
    }
}
