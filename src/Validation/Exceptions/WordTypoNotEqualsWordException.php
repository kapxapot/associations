<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordTypoNotEqualsWordException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Typo must differ from the word.'
        ]
    ];
}
