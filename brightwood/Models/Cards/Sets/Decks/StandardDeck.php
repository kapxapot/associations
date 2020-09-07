<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

/**
 * 52-card french deck. Shuffled by default.
 */
class StandardDeck extends ShortDeck
{
    protected function build() : CardCollection
    {
        $suits = Suit::all();

        $ranks = [
            Rank::two(),
            Rank::three(),
            Rank::four(),
            Rank::five()
        ];

        $moreCards = [];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $moreCards[] = new SuitedCard($suit, $rank);
            }
        }

        return parent::build()->concat(
            CardCollection::make($moreCards)
        );
    }
}
