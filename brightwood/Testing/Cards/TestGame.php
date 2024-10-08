<?php

namespace Brightwood\Testing\Cards;

use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\TextMessage;

class TestGame extends CardGame
{
    public static function maxPlayers(): int
    {
        return 2;
    }

    protected function dealing(): MessageInterface
    {
        return new TextMessage('Dealing...');
    }
}
