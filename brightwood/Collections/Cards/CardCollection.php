<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Card;
use Plasticode\Collections\Basic\TypedCollection;

class CardCollection extends TypedCollection
{
    protected string $class = Card::class;
}
