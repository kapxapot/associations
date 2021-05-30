<?php

namespace App\Exceptions;

class DuplicateWordException extends TurnException
{
    protected function getMessageTemplate(): string
    {
        return 'Word "%s" is already used in the game.';
    }
}
