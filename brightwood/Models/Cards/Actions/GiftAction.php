<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;

abstract class GiftAction implements \JsonSerializable
{
    protected Card $card;
    protected ?Player $sender;

    public function __construct(
        Card $card,
        ?Player $sender = null
    )
    {
        $this->card = $card;
        $this->sender = $sender;
    }

    public function card() : Card
    {
        return $this->card;
    }

    public function sender() : ?Player
    {
        return $this->sender;
    }

    /**
     * Returns announcement events, that will be consumed on gift creation.
     */
    abstract public function announcementEvents() : CardEventCollection;

    // JsonSerializable

    public function jsonSerialize()
    {
        return [
            'card' => $this->card,
            'sender' => $this->sender
        ];
    }
}
