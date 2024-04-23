<?php

namespace App\Domain\Client\Form;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewClientForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'fullname', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Nom complet',
                    'class' => 'form-input-md',
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Téléphone',
                    'class' => 'form-input-md',
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-input-md',
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'dateOfBirthday', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'Date de naissance',
                    'class' => 'form-input-md flatpickr-date-birthday',
                ],
                'required' => false,
                'label_attr' => [
                    'class' => 'label',
                ],
            ] );

    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => User::class,
        ] );
    }
}