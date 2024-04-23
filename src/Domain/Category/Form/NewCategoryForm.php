<?php

namespace App\Domain\Category\Form;

use App\Domain\Category\Entity\Category;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewCategoryForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'class' => 'form-input-md',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'description', TextareaType::class, [
                'label' => 'Description de la catégorie',
                'attr' => [
                    'class' => 'form-input-md',
                ],
                'required' => false,
                'label_attr' => [
                    'class' => 'label',
                ],
            ] )
            ->add( 'isActive', SwitchType::class, [
                'label' => 'En ligne ?',
            ] );
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => Category::class,
        ] );
    }
}
