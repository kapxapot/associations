<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

class SkipGiftAction extends GiftAction implements ApplicableActionInterface
{
    public function initialEvents() : CardEventCollection
    {
        return CardEventCollection::collect(
            new PublicEvent('Следующий игрок пропускает ход')
        );
    }

    public function applyTo(CardGame $game, Player $player) : CardEventCollection
    {
        return CardEventCollection::collect(
            new SkipEvent($player)
        );
    }
}
