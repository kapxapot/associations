<?php

namespace Brightwood\Models\Cards\Restrictions\Interfaces;

use Brightwood\Models\Cards\Card;
use Brightwood\Serialization\Interfaces\SerializableInterface;

interface RestrictionInterface extends SerializableInterface
{
    /**
     * Returns true if the card falls under the restriction.
     */
    function isCompatible(Card $card) : bool;

    /**
     * String representation of the restriction.
     */
    function toString() : string;
}
