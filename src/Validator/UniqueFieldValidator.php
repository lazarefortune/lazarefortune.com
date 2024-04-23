<?php

namespace App\Validator;

use App\Infrastructure\Orm\AbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFieldValidator extends ConstraintValidator
{
    public function __construct( private readonly EntityManagerInterface $em )
    {
    }

    public function validate( mixed $obj, Constraint $constraint ) : void
    {
        if ( null === $obj || '' === $obj ) {
            return;
        }

        if ( !$constraint instanceof UniqueField ) {
            throw new \RuntimeException( sprintf( '%s ne peut pas valider des contraintes %s', self::class, $constraint::class ) );
        }

        if ( !method_exists( $obj, 'getId' ) ) {
            throw new \RuntimeException( sprintf( '%s ne peut pas être utilisé sur l\'objet %s car il ne possède pas de méthode getId()', self::class, $obj::class ) );
        }

        $value = $obj->{$constraint->field};

        $repository = $this->em->getRepository( $constraint->entityClass );

        if ( $repository instanceof AbstractRepository ) {
            $result = $repository->findOneByCaseInsensitive( [
                $constraint->field => $value,
            ] );
        } else {
            $result = $repository->findOneBy( [
                $constraint->field => $value,
            ] );
        }

        if ( null !== $result && ( !method_exists( $result, 'getId' ) || $result->getId() !== $obj->getId() ) ) {
            $this->context->buildViolation( $constraint->message )
                ->setParameter( '{{ value }}', $value )
                ->atPath( $constraint->field )
                ->addViolation();
        }
    }
}