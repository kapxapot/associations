<?php

namespace Brightwood\Tests\Models\Cards;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Testing\Models\TestDeck;
use Brightwood\Testing\Models\TestGame;
use PHPUnit\Framework\TestCase;

final class CardGameTest extends TestCase
{
    public function testOperations() : void
    {
        $bot1 = new Bot('Bot1');
        $bot2 = new Bot('Bot2');

        $game = new TestGame(
            new TestDeck(false),
            new Pile(),
            PlayerCollection::collect($bot1, $bot2)
        );

        $this->assertEquals(
            '♠6, ♠7, ♠8, ♣6, ♣7, ♣8, ♥6, ♥7, ♥8, ♦6, ♦7, ♦8',
            $game->deck()->toString()
        );

        // dealing 3 cards to every player

        $game->deal(3);

        $this->assertEquals(
            '♠6, ♠8, ♣7',
            $bot1->hand()->toString()
        );

        $this->assertEquals(
            '♠7, ♣6, ♣8',
            $bot2->hand()->toString()
        );

        // drawing 1 card to discard

        $game->drawToDiscard();

        $this->assertEquals(
            '♥6',
            $game->discard()->toString()
        );

        $this->assertEquals(
            '♥7, ♥8, ♦6, ♦7, ♦8',
            $game->deck()->toString()
        );

        // one card from deck to discard

        $game->drawToDiscard();

        $this->assertEquals(
            '♥7, ♥6',
            $game->discard()->toString()
        );

        $this->assertEquals(
            '♥8, ♦6, ♦7, ♦8',
            $game->deck()->toString()
        );

        // Bot1 draws 2 cards from deck

        $game->drawToHand($bot1, 2);

        $this->assertEquals(
            '♠6, ♠8, ♣7, ♥8, ♦6',
            $bot1->hand()->toString()
        );

        $this->assertEquals(
            '♦7, ♦8',
            $game->deck()->toString()
        );

        // Bot2 takes 1 card from discard

        $game->takeFromDiscard($bot2);

        $this->assertEquals(
            '♠7, ♣6, ♣8, ♥7',
            $bot2->hand()->toString()
        );

        $this->assertEquals(
            '♥6',
            $game->discard()->toString()
        );
    }
}
