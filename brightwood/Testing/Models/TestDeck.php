<?php

namespace Brightwood\Testing\Models;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Decks\Deck;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

class TestDeck extends Deck
{
    protected function build() : CardCollection
    {
        $suits = Suit::all();

        $ranks = [
            Rank::six(),
            Rank::seven(),
            Rank::eight()
        ];

        $cards = [];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $cards[] = new SuitedCard($suit, $rank);
            }
        }

        return CardCollection::make($cards);
    }
}
