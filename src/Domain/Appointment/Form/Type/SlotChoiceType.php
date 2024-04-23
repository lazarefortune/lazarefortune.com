<?php

namespace App\Domain\Appointment\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotChoiceType extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options )
    {
    }

    public function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setDefaults( [
            'multiple' => false,
            'expanded' => true,
        ] );
    }

    public function getBlockPrefix() : string
    {
        return 'slot_choice';
    }

    public function getParent() : string
    {
        return ChoiceType::class;
    }
}