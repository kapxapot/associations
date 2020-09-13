<?php

namespace Brightwood\Models\Cards\Moves\Actions\Eights;

use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Moves\Actions\GiftAction;
use Brightwood\Models\Cards\Moves\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Moves\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Players\Player;

class SixGiftAction extends GiftAction implements ApplicableActionInterface, SkipActionInterface
{
    public function getMessage() : string
    {
        return 'Следующий игрок тянет 1 карту и пропускает ход';
    }

    /**
     * @return string[] Message lines.
     */
    public function applyTo(CardGame $game, Player $player) : array
    {
        $lines = [];

        if ($game->deckSize() > 0) {
            $drawn = $game->drawToHand($player);

            if ($drawn->any()) {
                $lines[] = $player . ' тянет ' . $drawn . ' из колоды';
            }
        }

        return $lines;
    }
}
