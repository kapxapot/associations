<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\SuitedCard;

class CardCollection extends EquatableCollection
{
    protected string $class = Card::class;

    public function filterSuited() : SuitedCardCollection
    {
        return SuitedCardCollection::from(
            $this->where(
                fn (Card $c) => $c instanceof SuitedCard
            )
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
