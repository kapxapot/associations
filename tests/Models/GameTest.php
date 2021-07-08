<?php

namespace App\Tests;

use App\Collections\TurnCollection;
use App\Models\Game;
use App\Models\Turn;
use App\Models\Word;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    public function testTurnNameZeroTurns(): void
    {
        $game = (new Game())->withTurns(
            TurnCollection::empty()
        );

        $this->assertEquals('', $game->turnName());
    }

    public function testTurnNameOneTurn(): void
    {
        $game = (new Game())->withTurns(
            TurnCollection::collect(
                (new Turn())->withWord(new Word(['word' => 'w1']))
            )
        );

        $this->assertEquals('w1', $game->turnName());
    }

    public function testTurnNameTwoTurns(): void
    {
        $game = (new Game())->withTurns(
            TurnCollection::collect(
                (new Turn())->withWord(new Word(['id' => 1, 'word' => 'w1'])),
                (new Turn())->withWord(new Word(['id' => 2, 'word' => 'w2']))
            )->reverse()
        );

        $this->assertEquals('w1 ... w2', $game->turnName());
    }
}
