<?php

namespace App\Domain\Auth\Core\Entity;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Password\Entity\PasswordReset;
use App\Domain\Auth\Registration\Entity\EmailVerification;
use App\Domain\Notification\Entity\Notifiable;
use App\Domain\Premium\Entity\PremiumTrait;
use DateTimeInterface;
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
#[ORM\Table( name: 'user' )]
#[Vich\Uploadable]
#[UniqueEntity( fields: ['email'], message: 'There is already an account with this email' )]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Notifiable;
    use PremiumTrait;

    public const DAYS_FOR_PREVENT_DELETE_UNVERIFIED_USER = 4;
    public const DAYS_BEFORE_DELETE_UNVERIFIED_USER = 7;
    public const DAYS_FOR_PREVENT_DELETE_USER = 3;
    public const DAYS_BEFORE_DELETION = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 180, unique: true )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $fullname = null;

    #[ORM\Column( type: 'boolean' )]
    private bool $isVerified = false;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $phone = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?DateTimeInterface $date_of_birthday = null;

    #[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
    private ?string $avatar = null;

    #[Vich\UploadableField( mapping: 'avatar_images', fileNameProperty: 'avatar' )]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true, options: ['default' => 'FR'])]
    private ?string $country = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?DateTimeInterface $lastLogin = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?DateTimeInterface $deletedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $bannedAt = null;

    #[ORM\OneToMany( mappedBy: 'author', targetEntity: EmailVerification::class, orphanRemoval: true )]
    private Collection $emailVerifications;

    #[ORM\Column]
    private ?bool $cgu = null;

    #[ORM\Column( nullable: true )]
    private ?bool $isRequestDelete = null;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $stripeId = null;

    #[ORM\Column( type: 'string', nullable: true, options: ['default' => null] )]
    private ?string $invoiceInfo = null;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $apiKey = null;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $googleId = null;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $githubId = null;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $googleEmail = null;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $githubEmail = null;


    #[ORM\Column(type: Types::INTEGER, options: ['default'=>0] )]
    private int $xp = 0;

    #[ORM\Column(type: Types::INTEGER, options:['default'=>0])]
    private int $quizzesCompleted = 0;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: PasswordReset::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $passwordResets;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isNewsletterSubscribed = false;

    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private ?string $unsubscribeNewsletterToken = null;

    public function __construct()
    {
        $this->fullname = '';
        $this->email = '';
        $this->phone = '';
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->emailVerifications = new ArrayCollection();
        $this->passwordResets = new ArrayCollection();
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
    public function eraseCredentials() : void
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

    public function getCountry(): string
    {
        return $this->country ?: 'FR';
    }

    public function setCountry(?string $country): User
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt() : ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt() : ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt() : ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setCreatedAt( ?DateTimeInterface $createdAt ) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt( ?DateTimeInterface $updatedAt ) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function setDeletedAt( ?DateTimeInterface $deletedAt ) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDateOfBirthday() : ?DateTimeInterface
    {
        return $this->date_of_birthday;
    }

    public function setDateOfBirthday( ?DateTimeInterface $date_of_birthday ) : self
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

        if ( $avatarFile ) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function __sleep() : array
    {
        $vars = get_object_vars( $this );
        $excluded = ['avatarFile']; //propriétés à exclure.
        $result = [];
        foreach ( array_keys( $vars ) as $key ) {
            if ( !in_array( $key, $excluded ) && isset( $this->$key ) ) {
                $result[] = $key;
            }
        }
        return $result;
    }

    public function __wakeup()
    {
        $this->avatarFile = null;
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

    public function getApiKey() : ?string
    {
        return $this->apiKey;
    }

    public function setApiKey( ?string $apiKey ) : User
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setGoogleId( ?string $googleId ) : User
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getGoogleId() : ?string
    {
        return $this->googleId;
    }

    public function setGithubId( ?string $githubId ) : User
    {
        $this->githubId = $githubId;
        return $this;
    }

    public function getGithubId() : ?string
    {
        return $this->githubId;
    }

    public function getGoogleEmail(): ?string
    {
        return $this->googleEmail;
    }

    public function setGoogleEmail(?string $googleEmail): self
    {
        $this->googleEmail = $googleEmail;
        return $this;
    }

    public function getGithubEmail(): ?string
    {
        return $this->githubEmail;
    }

    public function setGithubEmail(?string $githubEmail): self
    {
        $this->githubEmail = $githubEmail;
        return $this;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function addXp(int $amount): self
    {
        $this->xp += $amount;
        return $this;
    }

    public function getQuizzesCompleted(): int
    {
        return $this->quizzesCompleted;
    }

    public function incrementQuizzesCompleted(): self
    {
        $this->quizzesCompleted++;
        return $this;
    }

    /**
     * @return Collection<int, PasswordReset>
     */
    public function getPasswordResets(): Collection
    {
        return $this->passwordResets;
    }

    public function addPasswordReset(PasswordReset $passwordReset): self
    {
        if (!$this->passwordResets->contains($passwordReset)) {
            $this->passwordResets->add($passwordReset);
            $passwordReset->setAuthor($this);
        }

        return $this;
    }

    public function removePasswordReset(PasswordReset $passwordReset): self
    {
        if ($this->passwordResets->removeElement($passwordReset)) {
            if ($passwordReset->getAuthor() === $this) {
                $passwordReset->setAuthor(null);
            }
        }

        return $this;
    }

    public function getInvoiceInfo(): ?string
    {
        return $this->invoiceInfo;
    }

    public function setInvoiceInfo(?string $invoiceInfo): User
    {
        $this->invoiceInfo = $invoiceInfo;

        return $this;
    }

    public function isNewsletterSubscribed(): bool
    {
        return $this->isNewsletterSubscribed;
    }

    public function setNewsletterSubscribed(bool $subscribe): self
    {
        $this->isNewsletterSubscribed = $subscribe;
        return $this;
    }

    public function getUnsubscribeNewsletterToken(): string
    {
        if ($this->unsubscribeNewsletterToken === null) {
            $this->unsubscribeNewsletterToken = bin2hex(random_bytes(16));
        }
        return $this->unsubscribeNewsletterToken;
    }

    public function setUnsubscribeNewsletterToken(string $token): self
    {
        $this->unsubscribeNewsletterToken = $token;
        return $this;
    }


//    public function __sleep()
//    {
//        return array_diff(array_keys(get_object_vars($this)), ['avatarFile']);
//    }
    public function hasRole( $role ) : bool
    {
        return in_array( $role , $this->roles );
    }
}
