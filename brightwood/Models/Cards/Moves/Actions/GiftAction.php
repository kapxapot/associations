<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;

class GiftAction extends Action
{
    private Player $sender;
    private Card $gift;

    public function __construct(
        Player $sender,
        Card $gift
    )
    {
        $this->sender = $sender;
        $this->gift = $gift;
    }

    public function sender() : Player
    {
        return $this->sender;
    }

    public function gift() : Card
    {
        return $this->gift;
    }
}
