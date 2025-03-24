<?php

namespace App\Domain\Contact\Dto;

use App\Domain\Contact\Entity\Contact;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ContactData
{
    #[Assert\NotBlank]
    #[Assert\Length( min: 2, max: 255 )]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length( min: 2, max: 255 )]
    public string $subject;

    #[Assert\NotBlank]
    #[Assert\Length( min: 10, max: 255 )]
    public string $message;

    public ?File $imageFile = null;

    public function __construct(
        private readonly Contact $contact
    ) {
        $this->name = $contact->getName() ?? '';
        $this->email = $contact->getEmail() ?? '';
        $this->subject = $contact->getSubject() ?? '';
        $this->message = $contact->getMessage() ?? '';
    }
}