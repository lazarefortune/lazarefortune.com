<?php

namespace App\Domain\Tag\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Domain\Prestation\Entity\Prestation;
use App\Domain\Tag\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity( repositoryClass: TagRepository::class )]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            defaults: ['id' => null],
            requirements: ['id' => '\d+'],
            normalizationContext: ['groups' => ["tag:read:collection", "tag:read:item"]],
        ),
        new Post(
            defaults: ['name' => null],
            requirements: ['name' => '\w+'],
            denormalizationContext: ['groups' => ['tag:write:item']],
        ),
    ],
    normalizationContext: ['groups' => ['tag:read:collection']],
    denormalizationContext: ['groups' => ['tag:write:collection']],
)]
#[UniqueEntity( fields: ['name'], message: 'Ce tag existe déjà' )]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255, unique: true )]
    #[Assert\NotBlank]
    #[Assert\Length( min: 3, max: 255 )]
    #[Groups( ["tag:read:collection", "tag:read:item", "tag:write:item"] )]
    private ?string $name = null;

    #[ORM\ManyToMany( targetEntity: Prestation::class, mappedBy: 'tags' )]
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

    /**
     * @return Collection<int, Prestation>
     */
    public function getPrestations() : Collection
    {
        return $this->prestations;
    }

    public function addPrestation( Prestation $prestations ) : self
    {
        if ( !$this->prestations->contains( $prestations ) ) {
            $this->prestations->add( $prestations );
            $prestations->addTag( $this );
        }

        return $this;
    }

    public function removePrestation( Prestation $prestations ) : self
    {
        if ( $this->prestations->removeElement( $prestations ) ) {
            $prestations->removeTag( $this );
        }

        return $this;
    }
}
