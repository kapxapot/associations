<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;
use Plasticode\Collections\Generic\EquatableCollection;
use Plasticode\Semantics\Sentence;

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

    /**
     * Sorts the cards and returns a new collection.
     *
     * @return static
     */
    public function sort(callable $sortFunc): self
    {
        $data = $this->data;
        usort($data, $sortFunc);

        return new static($data);
    }

    /**
     * Sorts the cards in reverse order and returns a new collection.
     *
     * @return static
     */
    public function sortReverse(callable $sortFunc): self
    {
        $data = $this->data;
        usort(
            $data,
            fn (Card $a, Card $b) => -1 * ($sortFunc)($a, $b)
        );

        return new static($data);
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

    public function toRuString(): string
    {
        return Sentence::join(
            $this->stringize(
                fn (Card $c) => $c->toRuString()
            )
        );
    }

    public function toHomogeneousString(): string
    {
        return Sentence::homogeneousJoin(
            $this->stringize()
        );
    }

    public function toHomogeneousRuString(): string
    {
        return Sentence::homogeneousJoin(
            $this->stringize(
                fn (Card $c) => $c->toRuString()
            )
        );
    }
}
