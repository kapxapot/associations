<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;

/**
 * A card list that allows adding (by 1+) cards.
 */
abstract class ExtendableCardList extends CardList
{
    /**
     * @return static
     */
    public function add(Card $card) : self
    {
        $this->cards = $this->cards->add($card);

        return $this;
    }

    /**
     * @return static
     */
    public function addMany(CardCollection $cards) : self
    {
        $this->cards = $this->cards->concat($cards);

        return $this;
    }
}
