<?php

namespace App\Domain\Payment\Entity;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Auth\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Transaction
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column( type: Types::INTEGER )]
    private ?int $id = null;

    #[ORM\Column( type: Types::INTEGER )]
    private int $amount;

    #[ORM\Column( type: Types::STRING, length: 255 )]
    private string $status;

    #[ORM\ManyToOne( inversedBy: 'transactions' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?User $client = null;

    #[ORM\OneToMany( mappedBy: 'transaction', targetEntity: Appointment::class )]
    private Collection $appointments;

    #[ORM\OneToMany( mappedBy: 'transaction', targetEntity: Payment::class, orphanRemoval: true )]
    private Collection $payments;

    #[ORM\Column( type: Types::DATETIME_IMMUTABLE )]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column( type: Types::DATETIME_MUTABLE )]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->appointments = new ArrayCollection();
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

    public function getClient() : ?User
    {
        return $this->client;
    }

    public function setClient( ?User $client ) : static
    {
        $this->client = $client;

        return $this;
    }

    public function getPayments() : Collection
    {
        return $this->payments;
    }

    public function addPayment( Payment $payment ) : static
    {
        if ( !$this->payments->contains( $payment ) ) {
            $this->payments[] = $payment;
            $payment->setTransaction( $this );
        }

        return $this;
    }

    public function removePayment( Payment $payment ) : static
    {
        if ( $this->payments->removeElement( $payment ) ) {
            // set the owning side to null (unless already changed)
            if ( $payment->getTransaction() === $this ) {
                $payment->setTransaction( null );
            }
        }

        return $this;
    }

    public function addAppointment( Appointment $appointment ) : self
    {
        if ( !$this->appointments->contains( $appointment ) ) {
            $this->appointments[] = $appointment;
            $appointment->setTransaction( $this );
        }

        return $this;
    }

    public function removeAppointment( Appointment $appointment ) : self
    {
        if ( $this->appointments->removeElement( $appointment ) ) {
            // set the owning side to null (unless already changed)
            if ( $appointment->getTransaction() === $this ) {
                $appointment->setTransaction( null );
            }
        }

        return $this;
    }

    public function getAppointments() : Collection
    {
        return $this->appointments;
    }

    public function getCreatedAt() : \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt( \DateTimeImmutable $createdAt ) : static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt() : \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt( \DateTimeInterface $updatedAt ) : static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}