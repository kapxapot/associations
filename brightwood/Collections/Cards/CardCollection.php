<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;

class CardCollection extends EquatableCollection
{
    protected string $class = Card::class;

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
