<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;
use Plasticode\Collections\Basic\TypedCollection;

class CardCollection extends TypedCollection
{
    protected string $class = Card::class;

    public function contains(Card $card) : bool
    {
        return $this->anyFirst(
            fn (Card $c) => $c->equals($card)
        );
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        $cardNames = $this
            ->map(
                fn (Card $c) => $c->name()
            )
            ->toArray();

        return implode(', ', $cardNames);
    }
}
