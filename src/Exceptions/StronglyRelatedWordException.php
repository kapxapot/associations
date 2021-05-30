<?php

namespace App\Exceptions;

class StronglyRelatedWordException extends TurnException
{
    protected function getMessageTemplate(): string
    {
        return 'Related word "%s" is already used in the game.';
    }
}
