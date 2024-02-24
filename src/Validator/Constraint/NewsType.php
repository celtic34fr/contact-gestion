<?php

namespace Celtic34fr\ContactGestion\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NewsType extends Constraint
{
    public $message = 'La valeur "{{ string }}" n\'est pas une valeur valide de type d\'envois d\'information.';
    public $mode = 'strict';
}
