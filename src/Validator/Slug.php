<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Slug extends Regex
{
    public function __construct(array $options = [])
    {
        $options['pattern'] = '/^([a-z0-9A-Z]+\-)*[a-z0-9A-Z]+$/';
        $options['message'] = 'Le slug ne doit contenir que des lettres, des chiffres et des tirets';
        parent::__construct($options);
    }

    public function validatedBy(): string
    {
        return RegexValidator::class;
    }
}