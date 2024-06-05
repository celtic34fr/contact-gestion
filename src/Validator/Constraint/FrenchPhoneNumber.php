<?php

namespace Celtic34fr\ContactGestion\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class FrenchPhoneNumber extends Constraint
{
    /*
    * Any public properties become valid options for the annotation.
    * Then, they can be set via the class's constructor.
    */
    public $message = "The phone number '{{ value }}' is not a valid french phone number.";
}