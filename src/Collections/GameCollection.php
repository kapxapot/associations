<?php

namespace App\Collections;

use App\Models\Game;
use Plasticode\Collections\Basic\DbModelCollection;

class GameCollection extends DbModelCollection
{
    protected string $class = Game::class;
}
