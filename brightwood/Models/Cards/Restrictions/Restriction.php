<?php

namespace Brightwood\Models\Cards\Restrictions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;
use Brightwood\Serialization\UniformSerializer;

abstract class Restriction implements RestrictionInterface
{
    abstract public function isCompatible(Card $card): bool;

    public function __toString()
    {
        return $this->toString();
    }

    abstract function toString(): string;

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    public function serialize(array ...$data): array
    {
        return UniformSerializer::serialize($this, ...$data);
    }
}
