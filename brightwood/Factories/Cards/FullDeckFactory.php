<?php

namespace Brightwood\Factories\Cards;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Joker;

/**
 * Factory that produces a 52-card french deck with 2 jokers.
 */
class FullDeckFactory extends StandardDeckFactory
{
    protected function collectCards(): CardCollection
    {
        return parent::collectCards()
            ->add(
                new Joker(),
                new Joker()
            );
    }
}
