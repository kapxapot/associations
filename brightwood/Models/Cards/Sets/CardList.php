<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Collections\Cards\SuitedCardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;

/**
 * Just a bunch of cards.
 */
abstract class CardList implements SerializableInterface
{
    protected CardCollection $cards;

    public function __construct(?CardCollection $cards = null)
    {
        $this->cards = $cards ?? CardCollection::empty();
    }

    public function cards() : CardCollection
    {
        return $this->cards;
    }

    /**
     * @return $this
     */
    public function withCards(CardCollection $cards) : self
    {
        $this->cards = $cards;

        return $this;
    }

    public function size() : int
    {
        return $this->cards->count();
    }

    public function isEmpty() : bool
    {
        return $this->size() == 0;
    }

    public function contains(Card $card) : bool
    {
        return $this->cards->contains($card);
    }

    public function suitedCards() : SuitedCardCollection
    {
        return $this->cards->filterSuited();
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->cards->toString();
    }

    public function toHomogeneousString() : string
    {
        return $this->cards->toHomogeneousString();
    }

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return UniformSerializer::serialize(
            $this,
            ['cards' => $this->cards],
            ...$data
        );
    }
}
