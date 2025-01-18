<?php

namespace App\Domain\Auth\Registration\Entity;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Registration\Repository\EmailVerificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: EmailVerificationRepository::class )]
class EmailVerification
{
    public const TOKEN_EXPIRATION_TIME = 60 * 15;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255 )]
    private ?string $email = null;

    #[ORM\ManyToOne( inversedBy: 'emailVerifications' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?User $author = null;

    #[ORM\Column( length: 255 )]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail( string $email ) : self
    {
        $this->email = $email;

        return $this;
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

    public function getExpiresAt() : ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt( \DateTimeImmutable $expiresAt ) : self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired() : bool
    {
        $now = new \DateTimeImmutable();

        return $now > $this->expiresAt;
    }
}
