<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordRelationAvailableException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'A word relation with this type and main word already exists.'
        ]
    ];
}
