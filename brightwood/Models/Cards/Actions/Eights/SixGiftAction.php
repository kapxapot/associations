<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Events\Generic\PublicEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;

class SixGiftAction extends GiftAction implements ApplicableActionInterface, SkipActionInterface
{
    public function announcementEvents(): CardEventCollection
    {
        return CardEventCollection::empty();

        // return CardEventCollection::collect(
        //     new PublicEvent('Следующий игрок берет 1 карту и пропускает ход')
        // );
    }

    public function applyTo(CardGame $game, Player $player): CardEventCollection
    {
        $events = [];

        if (!$game->isDeckEmpty()) {
            $drawEvent = $game->drawToHand($player);

            if ($drawEvent) {
                $events[] = $drawEvent;
            }
        }

        $events[] = new SkipEvent($player, Rank::six()->nameRu());

        return CardEventCollection::make($events);
    }
}
