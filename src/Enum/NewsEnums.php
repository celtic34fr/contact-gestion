<?php

namespace Celtic34fr\ContactGestion\Enum;

use Celtic34fr\ContactCore\Traits\EnumToArray;

enum NewsEnums: string
{
    use EnumToArray;

    case All        = 'AC';    // toute communication
    case NewsLetter = 'NL';    // lettres d'informations
    case Commercial = 'CI';    // communications commerciales

    public function _toString(): string
    {
        return (string) $this->value;
    }

    public static function isValid(string $value): bool
    {
        $newsValuesTab = array_column(self::cases(), 'value');
        return in_array($value, $newsValuesTab);
    }
}
