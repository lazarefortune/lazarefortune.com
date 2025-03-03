<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Newsletter\Enum\NewsletterTargetGroupOptions;
use App\Http\Admin\Data\AutomaticCrudData;
use Symfony\Component\Validator\Constraints as Assert;

class NewsletterCrudData extends AutomaticCrudData
{
    #[Assert\NotBlank]
    public ?string $subject = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public ?string $content = null;

    #[Assert\NotBlank]
    public ?\DateTimeInterface $sendAt;

    public bool $isDraft = false;

    #[Assert\NotBlank]
    public NewsletterTargetGroupOptions $targetGroup = NewsletterTargetGroupOptions::ALL;

    public function hydrate(): void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt(new \DateTimeImmutable());
    }

}