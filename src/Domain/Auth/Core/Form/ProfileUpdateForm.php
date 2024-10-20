<?php

namespace App\Domain\Auth\Core\Form;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUpdateForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar',
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-input'
                ]
            ] )
            ->add( 'fullname', TextType::class, [
                'label' => 'Nom complet',
                'label_attr' => [
                    'class' => 'label'
                ],
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Nouveau nom'
                ]
            ] );
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}