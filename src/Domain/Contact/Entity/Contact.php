<?php

namespace App\Domain\Contact\Entity;

use App\Domain\Contact\Repository\ContactRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity( repositoryClass: ContactRepository::class )]
#[Vich\Uploadable]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 255 )]
    private ?string $name = null;

    #[ORM\Column( length: 255 )]
    private ?string $email = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $subject = null;

    #[ORM\Column( type: Types::TEXT )]
    private ?string $message = null;

    #[ORM\Column( type: Types::DATETIME_IMMUTABLE  )]
    private ?\DateTimeInterface $createdAt = null;

    #[Vich\UploadableField(mapping: 'contact_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName( string $name ) : static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail( string $email ) : static
    {
        $this->email = $email;

        return $this;
    }

    public function getSubject() : ?string
    {
        return $this->subject;
    }

    public function setSubject( ?string $subject ) : static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage() : ?string
    {
        return $this->message;
    }

    public function setMessage( string $message ) : static
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt( \DateTimeInterface $createdAt ) : static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }
}
