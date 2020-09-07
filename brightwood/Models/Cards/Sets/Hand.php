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
     * Removes the card from the hand.
     * If the card is absent, throws {@see \InvalidArgumentException}.
     * 
     * @throws \InvalidArgumentException
     */
    public function remove(Card $card) : self
    {
        Assert::true(
            $this->cards->any(
                fn (Card $c) => $c->equals($card)
            )
        );

        $this->cards = $this->cards->removeFirst(
            fn (Card $c) => $c->equals($card)
        );

        return $this;
    }
}
