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
        Card $card,
        Suit $suit,
        ?Player $sender = null
    )
    {
        parent::__construct($card, $sender);

        $this->suit = $suit;
    }

    public function announcementEvents() : CardEventCollection
    {
        $events = CardEventCollection::empty();

        if ($this->sender) {
            $events = $events->add(
                new SuitRestrictionEvent($this->sender, $this->suit)
            );
        }

        return $events->add(
            new PublicEvent('Следующий игрок должен положить <b>' . $this . '</b>')
        );
    }

    // RestrictingInterface

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

    // JsonSerializable

    public function jsonSerialize()
    {
        return [
            'suit' => $this->suit
        ];
    }
}
