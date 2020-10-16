<?php

namespace Brightwood\Models\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;
use Webmozart\Assert\Assert;

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
     * @return $this
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

    /**
     * Parses a card. If unsuccessful, throws {@see \InvalidArgumentException}.
     * 
     * @throws \InvalidArgumentException
     */
    public static function parse(?string $str) : self
    {
        $card = self::tryParse($str);

        Assert::notNull(
            $card,
            'Failed to parse the card: ' . $str
        );

        return $card;
    }

    /**
     * Tries to parse a card. If unsuccessful, returns null.
     */
    public static function tryParse(?string $str) : ?self
    {
        return SuitedCard::tryParse($str) ?? Joker::tryParse($str);
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
