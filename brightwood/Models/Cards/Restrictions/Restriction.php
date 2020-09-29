<?php

namespace Brightwood\Models\Cards\Restrictions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;

abstract class Restriction implements RestrictionInterface
{
    abstract public function isCompatible(Card $card) : bool;

    public function __toString()
    {
        return $this->toString();
    }

    abstract function toString() : string;

    // JsonSerializable

    public function jsonSerialize()
    {
        return [
            'type' => static::class
        ];
    }
}
