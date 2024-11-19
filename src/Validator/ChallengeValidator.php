<?php

namespace App\Validator;

use App\Domain\AntiSpam\ChallengeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChallengeValidator extends ConstraintValidator
{
    public function __construct(private readonly ChallengeInterface $challenge) {

    }
    /**
     * @param array{challenge: string, answer: string} $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint) : void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->challenge->verify($value['challenge'], $value['answer'] ?? '')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
