<?php

namespace Brightwood\Models\Cards\Events\Interfaces;

use Brightwood\Models\Cards\Players\Player;

interface CardEventInterface
{
    public function messageFor(?Player $player): string;
}
