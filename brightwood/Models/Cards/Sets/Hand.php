<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Webmozart\Assert\Assert;

class Hand
{
    private CardCollection $cards;

    public function __construct(CardCollection $cards)
    {
        $this->cards = $cards;
    }

    public function cards() : CardCollection
    {
        return $this->cards;
    }

    public function add(Card $card) : self
    {
        $this->cards = $this->cards->add($card);

        return $this;
    }

    public function addMany(CardCollection $newCards) : self
    {
        $this->cards = $this->cards->concat($newCards);

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function remove(Card $card) : self
    {
        $foundCards = $this->cards->where(
            fn (Card $c) => $c->equals($card)
        );

        Assert::notEmpty($foundCards);

        
    }
}
