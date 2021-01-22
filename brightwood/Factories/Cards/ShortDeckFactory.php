<?php

namespace Brightwood\Factories\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Factories\Cards\Interfaces\DeckFactoryInterface;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;

/**
 * Factory that produces a 36-card french deck.
 */
class ShortDeckFactory implements DeckFactoryInterface
{
    public function make(): Deck
    {
        return new Deck(
            $this->collectCards()
        );
    }

    protected function collectCards(): CardCollection
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
