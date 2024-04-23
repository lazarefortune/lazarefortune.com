<?php

namespace App\Domain\Realisation\Form;

use App\Domain\Realisation\Entity\Realisation;
use App\Http\Type\PriceType;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealisationForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'online', SwitchType::class, [
                'label' => 'Visible sur le site ?',
            ] )
            ->add( 'date', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'label' => 'Date de la réalisation',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'flatpickr-date-realisation form-input-md',
                    'data-input' => 'true'
                ],
            ] )
            ->add( 'images', FileType::class, [
                'label' => 'Ajoutez des images',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'inputfile inputfile-6',
                    'placeholder' => 'Images',
                    'data-multiple-caption' => '{count} fichiers sélectionnés',
                ],
            ] );
    }


    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => Realisation::class,
        ] );
    }
}
