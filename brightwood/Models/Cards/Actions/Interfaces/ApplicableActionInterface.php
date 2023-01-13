<?php

namespace Brightwood\Models\Cards\Actions\Interfaces;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

interface ApplicableActionInterface
{
    public function applyTo(CardGame $game, Player $player): CardEventCollection;
}
