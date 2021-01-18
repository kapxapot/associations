<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Generic\PlayerEvent;
use Brightwood\Models\Cards\Players\Player;
use Webmozart\Assert\Assert;

class DiscardEvent extends PlayerEvent
{
    protected CardCollection $cards;

    public function __construct(
        Player $player,
        Card ...$cards
    )
    {
        parent::__construct($player);

        Assert::minCount($cards, 1);

        $this->cards = CardCollection::make($cards);
    }

    public function publicChunk(): string
    {
        return 'кладет ' . $this->cards . ' на стол';
    }

    public function personalChunk(): string
    {
        return 'кладете ' . $this->cards . ' на стол';
    }
}
