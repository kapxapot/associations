<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Webmozart\Assert\Assert;

/**
 * A card list that allows shuffling and dealing.
 */
class Deck extends CardList
{
    /**
     * @return $this
     */
    public function shuffle() : self
    {
        $this->cards = $this->cards->shuffle();

        return $this;
    }

    /**
     * Removes first card from the deck and returns it.
     * Returns null in case of empty deck.
     */
    public function draw() : ?Card
    {
        return $this->drawMany(1)->first();
    }

    /**
     * Removes up to $amount first cards from the deck and returns them.
     * 
     * @throws \InvalidArgumentException
     */
    public function drawMany(int $amount) : CardCollection
    {
        Assert::greaterThan($amount, 0);

        $taken = $this->cards->take($amount);
        $this->cards = $this->cards->skip($amount);

        return $taken;
    }
}
