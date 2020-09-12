<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\SuitCollection;
use Brightwood\Collections\Cards\SuitedCardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\SuitedCard;

/**
 * Just a bunch of cards.
 */
abstract class CardList
{
    protected CardCollection $cards;

    public function __construct(?CardCollection $cards = null)
    {
        $this->cards = $cards ?? CardCollection::empty();
    }

    public function cards() : CardCollection
    {
        return $this->cards;
    }

    public function size() : int
    {
        return $this->cards->count();
    }

    public function isEmpty() : bool
    {
        return $this->size() == 0;
    }

    public function contains(Card $card) : bool
    {
        return $this->cards->contains($card);
    }

    public function suitedCards() : SuitedCardCollection
    {
        return $this->cards->filterSuited();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->cards->toString();
    }
}
