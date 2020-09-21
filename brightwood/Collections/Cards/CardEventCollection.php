<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Plasticode\Collections\Basic\TypedCollection;

class CardEventCollection extends TypedCollection
{
    protected string $class = CardEventInterface::class;
}
