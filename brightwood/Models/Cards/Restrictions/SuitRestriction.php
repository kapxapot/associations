<?php

namespace Brightwood\Models\Cards\Restrictions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Suit;
use Webmozart\Assert\Assert;

class SuitRestriction extends Restriction
{
    private ?Suit $suit = null;

    public function __construct(
        ?Suit $suit = null
    )
    {
        $this->suit = $suit;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function suit() : Suit
    {
        Assert::notNull($this->suit);

        return $this->suit;
    }

    /**
     * @return $this
     */
    public function withSuit(Suit $suit) : self
    {
        $this->suit = $suit;

        return $this;
    }

    public function isCompatible(Card $card): bool
    {
        return $card->isSuit($this->suit());
    }

    public function toString(): string
    {
        return $this->suit()->fullNameRu();
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['suit' => $this->suit()]
        );
    }
}
