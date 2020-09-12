<?php

namespace Brightwood\Models\Cards;

use Brightwood\Models\Interfaces\EquatableInterface;

abstract class Card implements EquatableInterface
{
    public function __toString()
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->name();
    }

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    abstract public function name(?string $lang = null) : string;

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    abstract public function fullName(?string $lang = null) : string;

    abstract public function equals(?EquatableInterface $obj) : bool;

    public function isJoker() : bool
    {
        return false;
    }

    public function isSuited() : bool
    {
        return false;
    }

    public function isSuit(Suit $suit) : bool
    {
        return false;
    }

    public function isRank(Rank $rank) : bool
    {
        return false;
    }
}
