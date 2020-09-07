<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Joker;

/**
 * 52-card french deck with 2 jokers. Shuffled by default.
 */
class FullDeck extends StandardDeck
{
    protected function build() : CardCollection
    {
        return parent::build()->concat(
            CardCollection::make([new Joker(), new Joker()])
        );
    }
}
