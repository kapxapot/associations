<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\DrawEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;

class SixGiftAction extends GiftAction implements ApplicableActionInterface
{
    public function initialEvents() : CardEventCollection
    {
        return CardEventCollection::collect(
            new PublicEvent('Следующий игрок берет 1 карту и пропускает ход')
        );
    }

    public function applyTo(CardGame $game, Player $player) : CardEventCollection
    {
        $events = [];

        if ($game->deckSize() > 0) {
            $drawn = $game->drawToHand($player);

            if ($drawn->any()) {
                $events[] = new DrawEvent($player, $drawn);
            }
        }

        $events[] = new SkipEvent(
            $player,
            Rank::six()->nameRu()
        );

        return CardEventCollection::make($events);
    }
}
