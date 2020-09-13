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

    /**
     * @return string[] Message lines.
     */
    public function getMessages() : array
    {
        $suitName = $this->suit()->fullNameRu();

        return [
            $this->sender . ' называет масть: <b>' . $suitName . '</b>',
            'Следующий игрок должен положить ' . $suitName
        ];
    }
}
