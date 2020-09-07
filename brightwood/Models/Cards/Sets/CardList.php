<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Webmozart\Assert\Assert;

class CardList
{
    protected CardCollection $cards;

    public function __construct(CardCollection $cards)
    {
        $this->cards = $cards;
    }

    public function cards() : CardCollection
    {
        return $this->cards;
    }

    /**
     * @return static
     */
    public function shuffle() : self
    {
        $this->cards = $this->cards->shuffle();

        return $this;
    }

    public function size() : int
    {
        return $this->cards->count();
    }

    public function draw() : ?Card
    {
        return $this->drawMany(1)->first();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function drawMany(int $count) : CardCollection
    {
        Assert::greaterThan($count, 0);

        $drawn = $this->cards->take($count);
        $this->cards = $this->cards->skip($count);

        return $drawn;
    }

    /**
     * @return static
     */
    public function reverse() : self
    {
        $this->cards = $this->cards->reverse();

        return $this;
    }
}
