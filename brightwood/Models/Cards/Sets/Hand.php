<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Models\Cards\Card;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * An extendable card list that allows removing cards (by 1).
 */
class Hand extends ExtendableCardList
{
    /**
     * Removes the card from the set.
     * If the card is absent, throws {@see InvalidArgumentException}.
     *
     * @throws InvalidArgumentException
     */
    public function remove(Card $card): self
    {
        Assert::true($this->contains($card));

        $this->cards = $this->cards->removeFirst(
            fn (Card $c) => $c->equals($card)
        );

        return $this;
    }
}
