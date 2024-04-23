<?php

namespace App\Domain\Payment\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column( type: Types::INTEGER )]
    private ?int $id = null;

    #[ORM\Column( type: Types::STRING, length: 255 )]
    private string $name;

    #[ORM\Column( type: Types::BOOLEAN, options: ['default' => false] )]
    private bool $isAvailableToClient;

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isAvailableToClient() : bool
    {
        return $this->isAvailableToClient;
    }

    public function setName( string $name ) : void
    {
        $this->name = $name;
    }

    public function setIsAvailableToClient( bool $isAvailableToClient ) : void
    {
        $this->isAvailableToClient = $isAvailableToClient;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}