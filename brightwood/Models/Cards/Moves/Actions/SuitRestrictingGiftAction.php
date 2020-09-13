<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;

class SuitRestrictingGiftAction extends RestrictingGiftAction
{
    protected Suit $suit;

    public function __construct(
        Player $sender,
        Card $gift,
        Suit $suit
    )
    {
        parent::__construct(
            $sender,
            $gift,
            fn (Card $c) => $c->isSuit($suit)
        );

        $this->suit = $suit;
    }

    public function suit() : Suit
    {
        return $this->suit;
    }

    public function getMessage(): string
    {
        return $this->sender . ' называет масть: ' . $this->suit();
    }
}
