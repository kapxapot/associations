<?php

namespace Brightwood\Collections\Cards;

use App\Semantics\Sentence;
use Brightwood\Models\Cards\Card;
use Plasticode\Collections\Generic\EquatableCollection;

class CardCollection extends EquatableCollection
{
    protected string $class = Card::class;

    public function filterSuited(): SuitedCardCollection
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

    public function toString(): string
    {
        return Sentence::join(
            $this->stringize()
        );
    }

    public function toHomogeneousString(): string
    {
        return Sentence::homogeneousJoin(
            $this->stringize()
        );
    }
}
