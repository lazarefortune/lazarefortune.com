<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class UniqueField extends Constraint
{
    public string $message = 'Cette valeur est déjà utilisée';
    public ?string $entityClass = null;
    public string $field = '';

    public function __construct(
        string $entityClass,
        string $field,
        string $message = 'Cette valeur est déjà utilisée',
        array  $groups = null,
        mixed  $payload = null
    )
    {
        parent::__construct(
            [
                'field' => $field,
                'message' => $message,
                'entityClass' => $entityClass,
            ], $groups, $payload
        );
    }

    public function validatedBy() : string
    {
        return static::class . 'Validator';
    }

    public function getTargets() : string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}