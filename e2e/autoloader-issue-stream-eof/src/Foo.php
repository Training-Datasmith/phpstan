<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class Foo extends ConstraintValidator
{
    /**
     * @param mixed $value
     *
     */
    public function validate($value, Constraint $constraint): void
    {
        $this->context->buildViolation('foo');
    }
}
