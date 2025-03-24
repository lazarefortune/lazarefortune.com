<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class DropZoneFileType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'mapped' => true,
            'label' => false,
            'attr' => [
                'class' => 'hidden',
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['dropzone'] = true;
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'dropzone';
    }
}
