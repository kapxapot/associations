<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\CardList;
use Webmozart\Assert\Assert;

/**
 * A specific card list that allows shuffling and dealing.
 * 
 * Shuffled by default.
 */
abstract class Deck extends CardList
{
    public function __construct(bool $shuffle = true)
    {
        parent::__construct(
            $this->build()
        );

        Assert::false($this->isEmpty());

        if ($shuffle) {
            $this->shuffle();
        }
    }

    abstract protected function build() : CardCollection;

    /**
     * @return static
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
