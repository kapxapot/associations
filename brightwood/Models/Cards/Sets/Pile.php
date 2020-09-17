<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Webmozart\Assert\Assert;

/**
 * A stack of cards that allows to put and take cards.
 * Starts empty.
 */
class Pile extends ExtendableCardList
{
    public function __construct()
    {
        parent::__construct();
    }

    public function top() : ?Card
    {
        return $this->cards->last();
    }

    public function take() : ?Card
    {
        return $this->takeMany(1)->first();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function takeMany(int $amount) : CardCollection
    {
        Assert::greaterThan($amount, 0);

        $taken = $this->cards->tail($amount);
        $this->cards = $this->cards->trimTail($amount);

        return $taken;
    }

    public function flip() : self
    {
        $this->cards = $this->cards->reverse();

        return $this;
    }

    public function toString(): string
    {
        return $this->cards->reverse()->toString();
    }
}
