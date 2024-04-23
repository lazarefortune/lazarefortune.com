<?php

namespace App\Validator;

use Attribute;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Contrainte pour vérifier l'unicité d'un enregistrement.
 */
#[Attribute( Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY )]
class Unique extends Constraint
{
    public string $message = 'Cette valeur est déjà utilisée';

    /**
     * @var class-string<object>|null
     */
    public ?string $entityClass = null;

    public string $field = '';

    #[HasNamedArguments]
    public function __construct(
        string $field = '',
        string $message = 'Cette valeur est déjà utilisée',
        string $entityClass = null,
        array  $groups = null,
        mixed  $payload = null
    )
    {
        parent::__construct( [
            'field' => $field,
            'message' => $message,
            'entityClass' => $entityClass,
        ], $groups, $payload );
    }

    #[NoReturn] public function getTargets() : string|array
    {
        return [ self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT ];
    }
}