<?php

namespace App\Domain\Application\Form;

use App\Domain\Application\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionForm extends AbstractType
{

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'label' )
            ->add( 'name' )
            ->add( 'value' )
            ->add( 'type' , ChoiceType::class , [
                'choices' => [
                    'Select' => ChoiceType::class,
                    'Text'   => TextType::class,
                ]
            ]);
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => Option::class,
        ] );
    }
}