<?php

namespace App\Http\Admin\Form\Field;

use App\Domain\Course\Entity\Technology;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnologyChoiceType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'class' => Technology::class,
            'multiple' => true,
            'attr' => [
                'class' => 'select2 form-input'
            ],
            'label_attr' => [
                'class' => 'label'
            ]
        ]);
    }
}