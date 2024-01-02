<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Currency extends Constraint
{
    public $message = 'The value \'{{ value }}\' is not a valid currency format.';
}