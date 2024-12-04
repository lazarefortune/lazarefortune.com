<?php

namespace App\Http\Type;

use App\Domain\Application\Entity\Content;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'class' => Content::class,
            'choice_label' => 'title',
            'attr' => [
                'class' => 'select2 form-input'
            ],
            'placeholder' => 'Sélectionnez un contenu',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}