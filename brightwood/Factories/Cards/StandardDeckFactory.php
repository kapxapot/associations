<?php

namespace Brightwood\Factories\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

/**
 * Factory that produces a 52-card french deck.
 */
class StandardDeckFactory extends ShortDeckFactory
{
    protected function collectCards(): CardCollection
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

        return parent::collectCards()->add(...$moreCards);
    }
}
