<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

class SevenGiftAction extends GiftAction implements ApplicableActionInterface, SkipActionInterface
{
    public function announcementEvents() : CardEventCollection
    {
        return CardEventCollection::collect(
            new PublicEvent('Следующий игрок берет 2 карты и пропускает ход')
        );
    }

    public function applyTo(CardGame $game, Player $player) : CardEventCollection
    {
        $events = [];

        $toDraw = min(2, $game->deckSize());

        while ($toDraw > 0) {
            $drawEvent = $game->drawToHand($player);

            if (is_null($drawEvent)) {
                break;
            }

            $events[] = $drawEvent;

            $toDraw--;
        }

        $events[] = new SkipEvent($player);

        return CardEventCollection::make($events);
    }
}