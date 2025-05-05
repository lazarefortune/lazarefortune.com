<?php

namespace App\Http\Type;

use App\Domain\Badge\Entity\BadgeAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeActionChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'choices' => BadgeAction::getChoices(),
            'attr' => [
                'class' => 'select2 form-input'
            ],
            'placeholder' => 'Sélectionnez une action',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

