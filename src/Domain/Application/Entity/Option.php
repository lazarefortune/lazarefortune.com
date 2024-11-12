<?php

namespace App\Domain\Application\Entity;

use App\Domain\Application\Repository\OptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table( name: '`option`' )]
#[ORM\Index( columns: ['key'], name: 'key_idx' )]
#[ORM\Entity( repositoryClass: OptionRepository::class )]
class Option
{
    #[ORM\Id]
    #[ORM\Column( '`key`', type: Types::STRING, length: 255 )]
    private string $key;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private string $value;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValue( string $value ) : self
    {
        $this->value = $value;

        return $this;
    }
}
