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
    private ?bool $isOnline = null;

    #[ORM\Column( type: Types::INTEGER, nullable: true )]
    private ?int $amount = null;

    #[ORM\Column( Types::BOOLEAN, nullable: true )]
    private ?bool $isAmountPublic = null;

    #[ORM\Column( nullable: true )]
    private ?\DateTime $dateRealisation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany( mappedBy: 'realisation', targetEntity: ImageRealisation::class, orphanRemoval: true )]
    private Collection $images;

    #[ORM\Column( type: Types::TIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $duration = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function isOnline() : ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnline( ?bool $isOnline ) : self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getAmount() : ?int
    {
        return $this->amount;
    }

    public function setAmount( ?int $amount ) : self
    {
        $this->amount = $amount;

        return $this;
    }

    public function isAmountPublic() : ?bool
    {
        return $this->isAmountPublic;
    }

    public function setIsAmountPublic( ?bool $isAmountPublic ) : self
    {
        $this->isAmountPublic = $isAmountPublic;

        return $this;
    }

    public function getDateRealisation() : ?\DateTime
    {
        return $this->dateRealisation;
    }

    public function setDateRealisation( ?\DateTime $dateRealisation ) : self
    {
        $this->dateRealisation = $dateRealisation;

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

    public function getDuration() : ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration( ?\DateTimeInterface $duration ) : self
    {
        $this->duration = $duration;

        return $this;
    }
}
