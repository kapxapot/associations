<?php

namespace Brightwood\Models\Cards\Moves\Actions\Interfaces;

use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

interface ApplicableActionInterface
{
    /**
     * @return string[] Message lines.
     */
    function applyTo(CardGame $game, Player $player) : array;
}
