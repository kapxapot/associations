<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Players\Player;
use Plasticode\Collections\Basic\TypedCollection;

class PlayerCollection extends TypedCollection
{
    protected string $class = Player::class;
}
