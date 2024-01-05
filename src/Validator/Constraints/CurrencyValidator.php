<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CurrencyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Currency) {
            throw new UnexpectedTypeException($constraint, Currency::class);
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}