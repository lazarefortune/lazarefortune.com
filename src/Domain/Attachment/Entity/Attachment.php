<?php

namespace App\Domain\Attachment\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class Attachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[Vich\UploadableField(mapping: 'attachments', fileNameProperty: 'fileName')]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private int $fileSize = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?: 0;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        if ( $file instanceof UploadedFile ) {
            $this->fileSize = $file->getSize(); // Assurez-vous que getSize retourne la bonne taille
        } else {
            $this->fileSize = 0; // Définissez une valeur par défaut si aucun fichier n'est fourni
        }

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName ?: '';

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize ?: 0;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->fileName ?: '';
    }


}