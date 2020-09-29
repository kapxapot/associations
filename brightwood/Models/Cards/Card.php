<?php

namespace Brightwood\Models\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;

abstract class Card implements EquatableInterface, \JsonSerializable
{
    private ?RestrictionInterface $restriction = null;

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

    /**
     * @return static
     */
    public function addRestriction(RestrictionInterface $restriction) : self
    {
        $this->restriction = $restriction;

        return $this;
    }

    public function hasRestriction() : bool
    {
        return $this->restriction !== null;
    }

    public function restriction() : ?RestrictionInterface
    {
        return $this->restriction;
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        return $this->hasRestriction()
            ? [
                'card' => $this->toString(),
                'restriction' => $this->restriction()
            ]
            : $this->toString();
    }
}
