<?php

namespace App\Http\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeType extends \Symfony\Component\Form\Extension\Core\Type\DateTimeType
{
    public function configureOptions( OptionsResolver $resolver ): void
    {
        parent::configureOptions( $resolver );
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => false,
            'label_attr' => [
                'class' => 'label',
            ],
            'attr' => [
                'class' => 'flatpickr-datetime form-input'
            ]
        ]);
    }
}