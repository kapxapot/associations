<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MainWordNonRecursiveException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Main word creates recursion.'
        ]
    ];
}
