<?php

namespace Brightwood\Testing\Models;

use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Message;

class TestGame extends CardGame
{
    public static function maxPlayers(): int
    {
        return 2;
    }

    protected function dealing() : MessageInterface
    {
        return new Message(['Dealing...']);
    }
}
