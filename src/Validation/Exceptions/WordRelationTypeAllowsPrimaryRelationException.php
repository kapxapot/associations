<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WordRelationTypeAllowsPrimaryRelationException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Word relation type doesn\'t allow a primary relation.'
        ]
    ];
}
