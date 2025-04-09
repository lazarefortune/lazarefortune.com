<?php

namespace App\Domain\Newsletter\Entity;

use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterSubscriberRepository::class)]
#[ORM\Table(name: 'newsletter_subscriber')]
class NewsletterSubscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Orm\Column(type: Types::STRING, length: 80, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isSubscribed = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $unsubscribeToken = null;

    public function __construct(string $name = '', string $email = '')
    {
        $this->name = $name;
        $this->email = $email;
        $this->isSubscribed = true;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function isSubscribed(): bool
    {
        return $this->isSubscribed;
    }

    public function setSubscribed(bool $subscribed): self
    {
        $this->isSubscribed = $subscribed;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUnsubscribeToken(): string
    {
        if ($this->unsubscribeToken === null) {
            // Génère un token de 32 caractères hexadécimaux (16 bytes)
            $this->unsubscribeToken = bin2hex(random_bytes(16));
        }
        return $this->unsubscribeToken;
    }

    public function setUnsubscribeToken(string $token): self
    {
        $this->unsubscribeToken = $token;
        return $this;
    }

    public function unsubscribe(): void
    {
        $this->isSubscribed = false;
    }
}
