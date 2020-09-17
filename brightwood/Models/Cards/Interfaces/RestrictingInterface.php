<?php

namespace Brightwood\Models\Cards\Interfaces;

use Brightwood\Models\Cards\Card;

interface RestrictingInterface
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
