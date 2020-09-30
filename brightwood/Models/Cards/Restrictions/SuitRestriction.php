<?php

namespace Brightwood\Models\Cards\Restrictions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Suit;

class SuitRestriction extends Restriction
{
    private Suit $suit;

    public function __construct(
        Suit $suit
    )
    {
        $this->suit = $suit;
    }

    public function isCompatible(Card $card): bool
    {
        return $card->isSuit($this->suit);
    }

    public function toString(): string
    {
        return $this->suit->fullNameRu();
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['suit' => $this->suit]
        );
    }
}
