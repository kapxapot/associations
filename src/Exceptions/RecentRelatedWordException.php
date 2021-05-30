<?php

namespace App\Exceptions;

class RecentRelatedWordException extends TurnException
{
    protected function getMessageTemplate(): string
    {
        return 'Related word "%s" is recently used in the game.';
    }
}
