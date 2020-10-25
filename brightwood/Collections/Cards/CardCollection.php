<?php

namespace Brightwood\Collections\Cards;

use App\Semantics\Sentence;
use Brightwood\Models\Cards\Card;
use Plasticode\Collections\Basic\ScalarCollection;

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
        return Sentence::join(
            $this->map(
                fn (Card $c) => $c->name()
            )
        );
    }

    public function toHomogeneousString() : string
    {
        return Sentence::homogeneousJoin(
            $this->map(
                fn (Card $c) => $c->name()
            )
        );
    }

    public function stringize() : ScalarCollection
    {
        return $this->scalarize(
            fn (Card $c) => $c->name()
        );
    }
}
