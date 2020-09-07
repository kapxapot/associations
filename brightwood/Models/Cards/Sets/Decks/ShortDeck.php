<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

/**
 * 36-card french deck. Shuffled by default.
 */
class ShortDeck extends Deck
{
    protected function build() : CardCollection
    {
        $suits = Suit::all();

        $ranks = [
            Rank::six(),
            Rank::seven(),
            Rank::eight(),
            Rank::nine(),
            Rank::ten(),
            Rank::jack(),
            Rank::queen(),
            Rank::king(),
            Rank::ace()
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
