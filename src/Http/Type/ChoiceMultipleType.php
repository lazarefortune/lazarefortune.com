<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceMultipleType extends ChoiceType
{
    public function getBlockPrefix() : string
    {
        return 'choice_multiple';
    }
}