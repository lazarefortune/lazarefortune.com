<?php

namespace App\Domain\Appointment\Entity;

use App\Domain\Appointment\Repository\AppointmentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Payment\Entity\Transaction;
use App\Domain\Prestation\Entity\Prestation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity( repositoryClass: AppointmentRepository::class )]
class Appointment
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_END = 'end';

    public static array $statusList = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELED,
        self::STATUS_END,
    ];

    public function isStatusPending() : bool
    {
        return $this->getStatus() === self::STATUS_PENDING;
    }

    public function isStatusConfirmed() : bool
    {
        return $this->getStatus() === self::STATUS_CONFIRMED;
    }

    public function isStatusCanceled() : bool
    {
        return $this->getStatus() === self::STATUS_CANCELED;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne( inversedBy: 'appointments' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?User $client = null;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $comment = null;

    #[ORM\Column( type: Types::INTEGER )]
    private int $nbAdults = 1;

    #[ORM\Column( type: Types::INTEGER )]
    private ?int $nbChildren = 0;

    #[ORM\Column( type: Types::DATE_MUTABLE )]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column( type: Types::TIME_MUTABLE )]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column( type: Types::TIME_MUTABLE )]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne( targetEntity: Transaction::class, inversedBy: 'appointments' )]
    #[ORM\JoinColumn( nullable: true )]
    private ?Transaction $transaction = null;

    #[ORM\ManyToOne( inversedBy: 'appointments' )]
    #[ORM\JoinColumn( nullable: false )]
    private ?Prestation $prestation = null;

    #[ORM\Column( type: Types::STRING, length: 50 )]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column( type: Types::INTEGER, nullable: true )]
    private ?int $subTotal = 0;

    #[ORM\Column( type: Types::INTEGER, nullable: true )]
    private ?int $total = 0;

    #[ORM\Column( type: Types::INTEGER, nullable: true )]
    private ?int $amountPaid = 0;

    #[ORM\Column( type: Types::INTEGER, nullable: true )]
    private ?int $appliedDiscount = 0;

    #[ORM\Column( type: Types::STRING, length: 50, nullable: true )]
    private ?string $accessToken = null;

    #[ORM\Column( type: Types::BOOLEAN )]
    private ?bool $isPaid = false;

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

    public function getClient() : ?User
    {
        return $this->client;
    }

    public function setClient( ?User $client ) : self
    {
        $this->client = $client;

        return $this;
    }

    public function getPrestation() : ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation( ?Prestation $prestation ) : self
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function setStatus( string $status ) : self
    {
        if ( !in_array( $status, self::$statusList, true ) ) {
            throw new \InvalidArgumentException( 'Le statut n\'est pas valide.' );
        }

        $this->status = $status;

        return $this;
    }

    public function getSubTotal() : ?int
    {
        return $this->subTotal;
    }

    public function setSubTotal( ?int $subTotal ) : self
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    public function getTotal() : ?int
    {
        return $this->total;
    }

    public function setTotal( ?int $total ) : self
    {
        $this->total = $total;

        return $this;
    }

    public function getAmountPaid() : ?int
    {
        return $this->amountPaid;
    }

    public function setAmountPaid( ?int $amountPaid ) : self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getAccessToken() : ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken( string $accessToken ) : self
    {
        $this->accessToken = $accessToken;

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

    public function getAppliedDiscount() : ?int
    {
        return $this->appliedDiscount;
    }

    public function setAppliedDiscount( ?int $appliedDiscount ) : self
    {
        $this->appliedDiscount = $appliedDiscount;
        return $this;
    }

    public function getTransaction() : ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction( ?Transaction $transaction ) : self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getComment() : ?string
    {
        return $this->comment;
    }

    public function setComment( ?string $comment ) : self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getNbAdults() : int
    {
        return $this->nbAdults;
    }

    public function setNbAdults( int $nbAdults ) : self
    {
        $this->nbAdults = $nbAdults;
        return $this;
    }

    public function getNbChildren() : ?int
    {
        return $this->nbChildren;
    }

    public function setNbChildren( ?int $nbChildren ) : self
    {
        $this->nbChildren = $nbChildren;
        return $this;
    }

    public function getDate() : ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate( ?\DateTimeInterface $date ) : self
    {
        $this->date = $date;
        return $this;
    }

    public function getStartTime() : ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime( ?\DateTimeInterface $startTime ) : self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime() : ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime( ?\DateTimeInterface $endTime ) : self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function isPaid() : ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid( ?bool $isPaid ) : self
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    public function isPassed() : bool
    {
        $now = new \DateTime();
        $appointmentEnd = new \DateTime( $this->date->format( 'Y-m-d' ) . ' ' . $this->endTime->format( 'H:i:s' ) );

        return $now > $appointmentEnd;
    }

    public function setIsConfirmed( bool $value ) : self
    {
        $this->setStatus( $value ? self::STATUS_CONFIRMED : self::STATUS_CANCELED );

        return $this;
    }

    /**
     * Met à jour le sous-total et le total de la réservation
     * @param int $discountValue
     * @param bool $isPercentage
     * @return $this
     */
    public function applyDiscount( int $discountValue, bool $isPercentage = false ) : self
    {
        if ( $isPercentage ) {
            // Calculez la réduction en tant que pourcentage du total
            $this->appliedDiscount = ( $this->total * $discountValue ) / 100;
        } else {
            // Appliquez directement le montant de la réduction
            $this->appliedDiscount = $discountValue;
        }

        // Ajustez le total après réduction
        $this->total -= $this->appliedDiscount;

        return $this;
    }

    public function unapplyDiscount() : self
    {
        $this->total += $this->appliedDiscount;
        $this->appliedDiscount = 0;

        return $this;
    }

    public function getRemainingAmount() : int
    {
        return $this->total - $this->amountPaid;
    }

    public function getDuration() : \DateTimeInterface
    {
        if ( $this->startTime && $this->endTime ) {
            $baseDate = new \DateTime( "00:00:00" );
            $interval = $this->startTime->diff( $this->endTime );
            return $baseDate->add( $interval );
        }

        return new \DateTime( "00:00:00" );
    }
}
