<?php

namespace App\Domain\Contact\Dto;

use App\Domain\Contact\Entity\Contact;
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

    public function __construct(
        private readonly Contact $contact
    )
    {
        $this->name = (string)$contact->getName();
        $this->email = (string)$contact->getEmail();
    }
}