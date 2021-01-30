<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordIsValidException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Word must contain only letters, digits, -, _ and \'.'
        ]
    ];
}
