<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;

abstract class GiftAction extends Action
{
    protected Player $sender;
    protected Card $card;

    public function __construct(
        Player $sender,
        Card $card
    )
    {
        $this->sender = $sender;
        $this->card = $card;
    }

    public function sender() : Player
    {
        return $this->sender;
    }

    public function card() : Card
    {
        return $this->card;
    }

    /**
     * Returns initial events, that will be consumes on gift creation.
     */
    abstract public function initialEvents() : CardEventCollection;
}
