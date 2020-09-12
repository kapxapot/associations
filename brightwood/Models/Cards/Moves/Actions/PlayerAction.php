<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Players\Player;

abstract class PlayerAction extends MessageAction
{
    abstract public function player() : Player;
    abstract public function privateMessage() : string;
}
