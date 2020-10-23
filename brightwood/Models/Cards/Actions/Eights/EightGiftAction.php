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
use Webmozart\Assert\Assert;

class EightGiftAction extends GiftAction
{
    private ?Suit $suit;

    public function __construct(
        ?Card $card = null,
        ?Suit $suit = null,
        ?Player $sender = null
    )
    {
        parent::__construct($card, $sender);

        $this->withSuit($suit);
    }

    public function suit() : Suit
    {
        Assert::notNull($this->suit);

        return $this->suit;
    }

    /**
     * @return $this
     */
    public function withSuit(?Suit $suit) : self
    {
        $this->suit = $suit;

        return $this;
    }

    public function announcementEvents() : CardEventCollection
    {
        $events = CardEventCollection::empty();

        if ($this->sender()) {
            $events = $events->add(
                new SuitRestrictionEvent($this->sender(), $this->suit())
            );
        }

        return $events->add(
            new PublicEvent(
                'Следующий игрок должен положить <b>' . $this->suit()->fullNameRu() . '</b>'
            )
        );
    }

    public function restriction() : RestrictionInterface
    {
        return new SuitRestriction($this->suit());
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return parent::serialize(
            ['suit' => $this->suit()]
        );
    }
}
