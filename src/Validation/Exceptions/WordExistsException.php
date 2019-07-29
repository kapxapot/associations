<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordExistsException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Word not found.'
        ]
    ];
}
