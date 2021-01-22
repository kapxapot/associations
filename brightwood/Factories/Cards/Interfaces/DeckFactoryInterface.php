<?php

namespace Brightwood\Factories\Cards\Interfaces;

use Brightwood\Models\Cards\Sets\Deck;

/**
 * Deck factory interface.
 */
interface DeckFactoryInterface
{
    function make(): Deck;
}
