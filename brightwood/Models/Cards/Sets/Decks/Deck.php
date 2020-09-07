<?php

namespace Brightwood\Models\Cards\Sets\Decks;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Sets\CardList;

abstract class Deck extends CardList
{
    public function __construct(bool $shuffle = true)
    {
        parent::__construct(
            $this->build()
        );

        if ($shuffle) {
            $this->shuffle();
        }
    }

    abstract protected function build() : CardCollection;
}
