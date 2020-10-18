<?php

namespace Brightwood\Testing\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Factories\Cards\Interfaces\DeckFactoryInterface;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

class TestDeckFactory implements DeckFactoryInterface
{
    public function make() : Deck
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

        return new Deck(
            CardCollection::make($cards)
        );
    }
}
