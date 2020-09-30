<?php

namespace Brightwood\Models\Cards\Restrictions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;
use Brightwood\Models\Cards\Traits\UniformSerialize;

abstract class Restriction implements RestrictionInterface
{
    use UniformSerialize;

    abstract public function isCompatible(Card $card) : bool;

    public function __toString()
    {
        return $this->toString();
    }

    abstract function toString() : string;

    // SerializableInterface

    public function serialize(array ...$data) : array
    {
        return $this->serializeRoot(...$data);
    }
}
