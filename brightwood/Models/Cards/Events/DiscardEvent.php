<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Events\Basic\PlayerEvent;
use Brightwood\Models\Cards\Players\Player;
use Webmozart\Assert\Assert;

class DiscardEvent extends PlayerEvent
{
    protected CardCollection $cards;

    public function __construct(
        Player $player,
        CardCollection $cards
    )
    {
        parent::__construct($player);

        Assert::minCount($cards, 1);

        $this->cards = $cards;
    }

    public function publicChunk() : string
    {
        return 'кладет ' . $this->cards . ' на стол';
    }

    public function personalChunk() : string
    {
        return 'кладете ' . $this->cards . ' на стол';
    }
}
