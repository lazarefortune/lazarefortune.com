<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class QuestionsForm extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        // On utilise un champ de type textarea pour stocker les données JSON
        $builder->addViewTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        // On définit le widget pour que le champ soit rendu comme un textarea
        $resolver->setDefaults([
            'attr' => ['class' => 'questions-editor'],
        ]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'questions';
    }

    /**
     * Transforme les données pour l'affichage dans le formulaire.
     *
     * @param array|null $value
     * @return string
     */
    public function transform($value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR) ?: '';
    }

    /**
     * Transforme les données soumises en données exploitables par l'application.
     *
     * @param string $value
     * @return array
     */
    public function reverseTransform($value): array
    {
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    /**
     * Personnalise le rendu du widget pour ajouter des attributs ou des classes CSS si nécessaire.
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {
        // Vous pouvez ajouter des attributs personnalisés ici si nécessaire
        // Par exemple, ajouter un data-attribute pour l'endpoint de recherche
        $view->vars['attr']['data-endpoint-search'] = '/admin/questions/search';
    }

}
