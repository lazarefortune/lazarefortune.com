<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form-input',
                'placeholder' => 'Numéro de téléphone',
                'autocomplete' => 'tel'
            ],
            'required' => false,
            'initial_country' => 'fr',
            'only_countries' => null,
            'wrapper_class' => 'phone-field-container',
        ]);

        $resolver->setAllowedTypes('initial_country', ['string', 'null']);
        $resolver->setAllowedTypes('only_countries', ['array', 'null']);
        $resolver->setAllowedTypes('wrapper_class', ['string', 'null']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Configuration des attributs pour le wrapper intl-tel-input
        $view->vars['wrapper_attr'] = [
            'class' => $options['wrapper_class'] ?? 'phone-field-container'
        ];

        if ($options['initial_country']) {
            $view->vars['wrapper_attr']['data-initial-country'] = $options['initial_country'];
        }

        if ($options['only_countries']) {
            $view->vars['wrapper_attr']['data-only-countries'] = implode(',', $options['only_countries']);
        }

        // Fusionner les attributs de l'input
        $view->vars['attr'] = array_merge(
            $view->vars['attr'],
            $options['attr']
        );
    }

    public function getParent(): string
    {
        return TelType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'phone';
    }
}
