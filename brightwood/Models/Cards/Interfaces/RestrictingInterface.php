<?php

namespace Brightwood\Models\Cards\Interfaces;

use Brightwood\Models\Cards\Card;

interface RestrictingInterface
{
    function restriction() : callable;

    /**
     * Returns true if the card falls under the restriction.
     */
    function isEligible(Card $card) : bool;
}
