<?php

namespace App\Domain\Auth\Core\Form;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUpdateForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'fullname', TextType::class, [
                'label' => 'Nom complet',
                'label_attr' => [
                    'class' => 'label'
                ],
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Nouveau nom'
                ]
            ] )
            ->add('date_of_birthday', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'html5' => false,
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'flatpickr-date-birthday form-input',
                    'data-input' => 'true'
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'label_attr' => [
                    'class' => 'label'
                ],
                'attr' => [
                    'class' => 'form-select',
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'label_attr' => ['class' => 'label'],
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Ex: 06 12 34 56 78'
                ]
            ])
            // Champ pour uploader un nouvel avatar
            ->add('avatarFile', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Nouvelle photo de profil',
                'label_attr' => ['class' => 'label'],
                'attr' => [
                    'class' => 'form-input',
                    'accept' => 'image/*'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}