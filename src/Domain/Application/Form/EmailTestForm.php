<?php

namespace App\Domain\Application\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailTestForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-input',
                'autocomplete' => 'off',
            ]
        ]);
    }
}