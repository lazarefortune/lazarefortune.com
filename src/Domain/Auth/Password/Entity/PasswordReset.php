<?php

namespace App\Domain\Auth\Password\Entity;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Password\Repository\PasswordResetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: PasswordResetRepository::class )]
class PasswordReset
{
    public const TOKEN_EXPIRATION_TIME = 60 * 15;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne( inversedBy: 'passwordResets' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?User $author = null;

    #[ORM\Column( length: 255 )]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getAuthor() : ?User
    {
        return $this->author;
    }

    public function setAuthor( ?User $author ) : self
    {
        $this->author = $author;

        return $this;
    }

    public function getToken() : ?string
    {
        return $this->token;
    }

    public function setToken( string $token ) : self
    {
        $this->token = $token;

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

    public function isExpired() : bool
    {
        return $this->createdAt->getTimestamp() + self::TOKEN_EXPIRATION_TIME < time();
    }
}