<?php

namespace App\Domain\Holiday\Form;

use App\Domain\Holiday\Entity\Holiday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HolidayForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'title', TextType::class, [
                'label' => 'Motif',
                'attr' => [
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input-md flatpickr-date-input',
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label',
                ]
            ] )
            ->add( 'endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input-md flatpickr-date-input',
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label',
                ]
            ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => Holiday::class,
        ] );
    }
}
