<?php

namespace App\Http\Type;

use App\Domain\Newsletter\Enum\NewsletterTargetGroupOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterTargetGroupType  extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'choices' => [
                'Tous le monde' => NewsletterTargetGroupOptions::ALL,
                'Utilisateurs' => NewsletterTargetGroupOptions::USERS,
                'Abonnés uniquement' => NewsletterTargetGroupOptions::SUBSCRIBERS,
            ],
            'attr' => [
                'class' => 'select2 form-input'
            ],
            'placeholder' => 'Sélectionnez un groupe cible',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}