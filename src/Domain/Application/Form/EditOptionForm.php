<?php

namespace App\Domain\Application\Form;

use App\Domain\Application\Entity\Option;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditOptionForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        /** @var Option $option */
        $option = $options['data'] ?? null;
        $type = null;

        $type = $option?->getType();

        if ( $type === CheckboxType::class ) {
            $builder->add( 'value', CheckboxType::class, [
                'label' => 'Valeur',
                'data' => boolval( $option?->getValue() ),
                'required' => false,
            ] );
        } else {
            $builder->add( 'value', TextType::class, [
                'label' => 'Valeur',
            ] );
        }

        $builder->add( 'submit', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => [
                'class' => 'btn-md btn-primary',
            ],
        ] );
    }

    public function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setDefaults( [
            'data_class' => null,
        ] );
    }

}