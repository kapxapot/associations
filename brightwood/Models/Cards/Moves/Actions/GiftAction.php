<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;

class GiftAction extends Action
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

    public function getMessage() : string
    {
        return 'Here is a gift for... someone';
    }
}
