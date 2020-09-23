<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Models\Cards\Events\Basic\PlayerEvent;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;

class SuitRestrictionEvent extends PlayerEvent
{
    protected Suit $suit;

    public function __construct(
        Player $player,
        Suit $suit
    )
    {
        parent::__construct($player);

        $this->suit = $suit;
    }

    public function publicChunk() : string
    {
        $suitName = $this->suit->fullNameRu();

        return 'называет масть: <b>' . $suitName . '</b>';
    }

    public function personalChunk() : string
    {
        $suitName = $this->suit->fullNameRu();

        return 'называете масть: <b>' . $suitName . '</b>';
    }
}
