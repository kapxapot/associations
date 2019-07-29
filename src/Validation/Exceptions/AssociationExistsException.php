<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class AssociationExistsException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Association not found.'
        ]
    ];
}
