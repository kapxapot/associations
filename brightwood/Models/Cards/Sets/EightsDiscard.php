<?php

namespace Brightwood\Models\Cards\Sets;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Joker;

class EightsDiscard extends Pile
{
    /**
     * If some jokers are on the top, the actual top is underneath them.
     */
    public function actualTop() : ?Card
    {
        $actual = $this->cards->last(
            fn (Card $c) => !($c instanceof Joker)
        );

        return $actual ?? $this->cards->last();
    }

    /**
     * Returns 
     *
     * @return string|null
     */
    public function topString() : ?string
    {
        $top = $this->top();

        if (is_null($top)) {
            return null;
        }

        $actual = $this->actualTop();

        if ($top->equals($actual) && !$actual->hasRestriction()) {
            return $top->toString();
        }

        $actualMixed = $actual->hasRestriction()
            ? $actual->restriction()
            : $actual;

        return $top . ' (' . $actualMixed . ')';
    }
}
