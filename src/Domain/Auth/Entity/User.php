<?php

namespace App\Domain\Auth\Entity;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Payment\Entity\Transaction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: '`user`' )]
#[Vich\Uploadable]
#[UniqueEntity( fields: ['email'], message: 'There is already an account with this email' )]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const DAYS_BEFORE_DELETE_UNVERIFIED_USER = 7;
    public const DAYS_BEFORE_DELETION = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 180, unique: true )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $fullname = null;

    #[ORM\Column( type: 'boolean' )]
    private bool $isVerified = false;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $phone = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $date_of_birthday = null;

    #[Vich\UploadableField( mapping: 'avatar_images', fileNameProperty: 'avatar' )]
    private ?File $avatarFile = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $avatar = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany( mappedBy: 'client', targetEntity: Appointment::class, orphanRemoval: true )]
    private Collection $appointments;

    #[ORM\OneToMany( mappedBy: 'author', targetEntity: EmailVerification::class, orphanRemoval: true )]
    private Collection $emailVerifications;

    #[ORM\OneToMany( mappedBy: 'client', targetEntity: Transaction::class, orphanRemoval: true )]
    private Collection $transactions;

    #[ORM\Column]
    private ?bool $cgu = null;

    #[ORM\Column( nullable: true )]
    private ?bool $isRequestDelete = null;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $stripeId = null;

    public function __construct()
    {
        $this->fullname = '';
        $this->email = '';
        $this->phone = '';
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->appointments = new ArrayCollection();
        $this->emailVerifications = new ArrayCollection();
    }

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier() : string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles() : array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique( $roles );
    }

    public function setRoles( array $roles ) : self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword( string $password ) : self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullname() : ?string
    {
        return $this->fullname;
    }

    public function setFullname( ?string $fullname ) : self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function isVerified() : bool
    {
        return $this->isVerified;
    }

    public function setIsVerified( bool $isVerified ) : self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPhone() : ?string
    {
        return $this->phone;
    }

    public function setPhone( ?string $phone ) : self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt() : ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setCreatedAt( ?\DateTimeInterface $createdAt ) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt( ?\DateTimeInterface $updatedAt ) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setDeletedAt( ?\DateTimeInterface $deletedAt ) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDateOfBirthday() : ?\DateTimeInterface
    {
        return $this->date_of_birthday;
    }

    public function setDateOfBirthday( ?\DateTimeInterface $date_of_birthday ) : self
    {
        $this->date_of_birthday = $date_of_birthday;

        return $this;
    }

    public function getAvatar() : ?string
    {
        return $this->avatar;
    }

    public function setAvatar( ?string $avatar ) : self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarFile() : ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile( ?File $avatarFile ) : self
    {
        $this->avatarFile = $avatarFile;

        // update updatedAt only if file is not null
        if ( $avatarFile ) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

//    public function __sleep() : array
//    {
//        // Exclure l'attribut avatarFile de la sérialisation
//        return array_diff( array_keys( get_object_vars( $this ) ), ['avatarFile', 'avatar'] );
//    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments() : Collection
    {
        return $this->appointments;
    }

    public function addAppointment( Appointment $appointment ) : self
    {
        if ( !$this->appointments->contains( $appointment ) ) {
            $this->appointments->add( $appointment );
            $appointment->setClient( $this );
        }

        return $this;
    }

    public function removeAppointment( Appointment $appointment ) : self
    {
        if ( $this->appointments->removeElement( $appointment ) ) {
            // set the owning side to null (unless already changed)
            if ( $appointment->getClient() === $this ) {
                $appointment->setClient( null );
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmailVerification>
     */
    public function getEmailVerifications() : Collection
    {
        return $this->emailVerifications;
    }

    public function addEmailVerification( EmailVerification $emailVerification ) : self
    {
        if ( !$this->emailVerifications->contains( $emailVerification ) ) {
            $this->emailVerifications->add( $emailVerification );
            $emailVerification->setAuthor( $this );
        }

        return $this;
    }

    public function removeEmailVerification( EmailVerification $emailVerification ) : self
    {
        if ( $this->emailVerifications->removeElement( $emailVerification ) ) {
            // set the owning side to null (unless already changed)
            if ( $emailVerification->getAuthor() === $this ) {
                $emailVerification->setAuthor( null );
            }
        }

        return $this;
    }

    public function isCgu() : ?bool
    {
        return $this->cgu;
    }

    public function setCgu( bool $cgu ) : static
    {
        $this->cgu = $cgu;

        return $this;
    }

    public function getTransactions() : Collection
    {
        return $this->transactions;
    }

    public function addTransaction( Transaction $transaction ) : static
    {
        if ( !$this->transactions->contains( $transaction ) ) {
            $this->transactions[] = $transaction;
            $transaction->setClient( $this );
        }

        return $this;
    }

    public function removeTransaction( Transaction $transaction ) : static
    {
        if ( $this->transactions->removeElement( $transaction ) ) {
            // set the owning side to null (unless already changed)
            if ( $transaction->getClient() === $this ) {
                $transaction->setClient( null );
            }
        }

        return $this;
    }

    public function isIsRequestDelete() : ?bool
    {
        return $this->isRequestDelete;
    }

    public function setIsRequestDelete( ?bool $isRequestDelete ) : static
    {
        $this->isRequestDelete = $isRequestDelete;

        return $this;
    }

    public function getStripeId() : ?string
    {
        return $this->stripeId;
    }

    public function setStripeId( ?string $stripeId ) : static
    {
        $this->stripeId = $stripeId;

        return $this;
    }

}
