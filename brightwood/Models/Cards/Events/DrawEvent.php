<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Events\Generic\PrivatePlayerEvent;
use Brightwood\Models\Cards\Players\Player;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

class DrawEvent extends PrivatePlayerEvent
{
    protected CardCollection $cards;

    private Cases $cases;

    public function __construct(
        Player $player,
        CardCollection $cards
    )
    {
        parent::__construct($player);

        Assert::minCount($cards, 1);

        $this->cards = $cards;

        $this->cases = new Cases();
    }

    public function cards(): CardCollection
    {
        return $this->cards;
    }

    public function publicChunk(): string
    {
        $count = $this->cards->count();
        $countStr = $this->cases->caseForNumber('карта', $count, Cases::ACC);

        return 'берет ' . $count . ' ' . $countStr . ' из колоды';
    }

    public function privateChunk(): string
    {
        return 'берет ' . $this->cards->toHomogeneousRuString() . ' из колоды';
    }

    public function personalChunk(): string
    {
        return 'берете ' . $this->cards->toHomogeneousRuString() . ' из колоды';
    }

    public function glue(self $other): self
    {
        $this->cards = $this->cards->concat($other->cards);

        return $this;
    }
}
