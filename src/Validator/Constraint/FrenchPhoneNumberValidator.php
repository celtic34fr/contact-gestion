<?php

namespace Celtic34fr\ContactGestion\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FrenchPhoneNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // Check if the phone number is a valid french phone number
        if (!preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', $value)) {
            // If the phone number is not valid, add a violation
            $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
        }
    }
}