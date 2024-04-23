<?php

namespace App\Domain\Realisation\Entity;

use App\Domain\Realisation\Repository\RealisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: RealisationRepository::class )]
class Realisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $online = null;

    #[ORM\Column( nullable: true )]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany( mappedBy: 'realisation', targetEntity: ImageRealisation::class, orphanRemoval: true )]
    private Collection $images;

    public function __construct()
    {
        $this->online = false;
        $this->date = new \DateTime();
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function isOnline() : ?bool
    {
        return $this->online;
    }

    public function setOnline( ?bool $isOnline ) : self
    {
        $this->online = $isOnline;

        return $this;
    }

    public function getDate() : ?\DateTime
    {
        return $this->date;
    }

    public function setDate( ?\DateTime $date ) : self
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedAt() : ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt( \DateTimeImmutable $createdAt ) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, ImageRealisation>
     */
    public function getImages() : Collection
    {
        return $this->images;
    }

    public function addImage( ImageRealisation $image ) : self
    {
        if ( !$this->images->contains( $image ) ) {
            $this->images->add( $image );
            $image->setRealisation( $this );
        }

        return $this;
    }

    public function removeImage( ImageRealisation $image ) : self
    {
        if ( $this->images->removeElement( $image ) ) {
            // set the owning side to null (unless already changed)
            if ( $image->getRealisation() === $this ) {
                $image->setRealisation( null );
            }
        }

        return $this;
    }
}
