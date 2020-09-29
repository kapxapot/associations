<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\GiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Basic\PublicEvent;
use Brightwood\Models\Cards\Events\SuitRestrictionEvent;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Restrictions\Interfaces\RestrictionInterface;
use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Suit;

class EightGiftAction extends GiftAction
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
            new PublicEvent(
                'Следующий игрок должен положить <b>' . $this->suit->fullNameRu() . '</b>'
            )
        );
    }

    public function restriction() : RestrictionInterface
    {
        return new SuitRestriction($this->suit);
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['data']['suit'] = $this->suit;

        return $data;
    }
}
