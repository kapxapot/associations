<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;

class CardCollection extends EquatableCollection
{
    protected string $class = Card::class;

    public function filterSuited() : SuitedCardCollection
    {
        return SuitedCardCollection::from(
            $this->where(
                fn (Card $c) => $c->isSuited()
            )
        );
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this
            ->map(
                fn (Card $c) => $c->name()
            )
            ->join(', ');
    }
}
