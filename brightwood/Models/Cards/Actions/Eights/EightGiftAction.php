<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\SuitRestrictionEvent;
use Brightwood\Models\Cards\Interfaces\RestrictingInterface;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Suit;

class EightGiftAction extends GiftAction implements RestrictingInterface
{
    protected Suit $suit;

    public function __construct(
        Player $sender,
        Card $gift,
        Suit $suit
    )
    {
        parent::__construct($sender, $gift);

        $this->suit = $suit;
    }

    /**
     * Returns true if the card falls under the restriction.
     */
    public function isCompatible(Card $card) : bool
    {
        return $card->isSuit($this->suit);
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * String representation of the restriction.
     */
    public function toString() : string
    {
        return $this->suit->fullNameRu();
    }

    public function initialEvents() : CardEventCollection
    {
        $suitName = $this->suit->fullNameRu();

        return CardEventCollection::collect(
            new SuitRestrictionEvent($this->sender, $this->suit),
            new PublicEvent('Следующий игрок должен положить <b>' . $suitName . '</b>')
        );
    }
}
