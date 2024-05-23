<?php

namespace App\Domain\Profile\Form;

use App\Domain\Profile\Dto\ProfileUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'fullname', TextType::class, [
                'label' => 'Nom complet',
                'label_attr' => [
                    'class' => 'label'
                ]
            ] )
            ->add( 'phone', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
                'label_attr' => [
                    'class' => 'label'
                ],
            ] )
            ->add( 'email', EmailType::class, [
                'label' => 'Adresse email',
                'label_attr' => [
                    'class' => 'label'
                ]
            ] )
            ->add( 'dateOfBirthday', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'flatpickr-date-birthday',
                ],
                'label_attr' => [
                    'class' => 'label'
                ],
                'required' => false,
            ] )
            ->add( 'avatarFile', FileType::class, [
                'label' => 'Avatar',
                'required' => false,
                'label_attr' => [
                    'class' => 'label'
                ]
            ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => ProfileUpdateData::class,
        ] );
    }
}