<?php

namespace App\Domain\Payment\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Payment
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column( type: Types::INTEGER )]
    private ?int $id = null;

    #[ORM\Column( type: Types::INTEGER )]
    private int $amount;

    #[ORM\Column( type: Types::STRING, length: 50 )]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
    private ?string $sessionId = null;

    #[ORM\Column( type: Types::STRING, length: 255 )]
    private string $paymentMethod;

    #[ORM\ManyToOne( inversedBy: 'payments' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?Transaction $transaction = null;

    #[ORM\Column( type: Types::DATETIME_IMMUTABLE )]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column( type: Types::DATETIME_MUTABLE )]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getAmount() : ?int
    {
        return $this->amount;
    }

    public function setAmount( int $amount ) : static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus() : ?string
    {
        return $this->status;
    }

    public function setStatus( string $status ) : static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentMethod() : ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod( string $paymentMethod ) : static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getCreatedAt() : ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt( \DateTimeImmutable $createdAt ) : static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt( \DateTimeInterface $updatedAt ) : static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTransaction() : ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction( ?Transaction $transaction ) : static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getSessionId() : ?string
    {
        return $this->sessionId;
    }

    public function setSessionId( ?string $sessionId ) : static
    {
        $this->sessionId = $sessionId;

        return $this;
    }
}