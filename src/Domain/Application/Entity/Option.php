<?php

namespace App\Domain\Application\Entity;

use App\Domain\Application\Repository\OptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: OptionRepository::class )]
#[ORM\Table( name: '`option`' )]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255 )]
    private ?string $label = null;

    #[ORM\Column( length: 255 )]
    private ?string $name = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $value = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $type = null;

    /**
     * @param string|null $label
     * @param string|null $name
     * @param string|null $value
     * @param string|null $type
     */
    public function __construct( ?string $label, ?string $name, mixed $value, mixed $type )
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setLabel( string $label ) : self
    {
        $this->label = $label;

        return $this;
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

    public function getValue() : ?string
    {
        return $this->value;
    }

    public function setValue( ?string $value ) : self
    {
        $this->value = $value;

        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType( string $type ) : self
    {
        $this->type = $type;

        return $this;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
