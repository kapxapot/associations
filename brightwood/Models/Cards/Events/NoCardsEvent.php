<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Models\Cards\Players\Player;

class NoCardsEvent extends SkipEvent
{
    public function __construct(
        Player $player
    )
    {
        parent::__construct($player, 'нет карт');
    }
}
