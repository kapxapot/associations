<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

class SkipGiftAction extends GiftAction implements ApplicableActionInterface
{
    protected ?string $reason;

    public function __construct(
        Card $card,
        ?Player $sender = null,
        ?string $reason = null
    )
    {
        parent::__construct($card, $sender);

        $this->reason = $reason;
    }

    public function announcementEvents() : CardEventCollection
    {
        return CardEventCollection::collect(
            new PublicEvent('Следующий игрок пропускает ход')
        );
    }

    public function applyTo(CardGame $game, Player $player) : CardEventCollection
    {
        return CardEventCollection::collect(
            new SkipEvent($player, $this->reason)
        );
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['reason' => $this->reason]
        );
    }
}
