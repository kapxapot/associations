<?php

namespace Brightwood\Models\Cards\Moves\Actions\Eights;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Interfaces\RestrictingInterface;
use Brightwood\Models\Cards\Moves\Actions\GiftAction;
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

    /**
     * String representation of the restriction.
     */
    public function restrictionStr() : string
    {
        return $this->suit->fullNameRu();
    }

    /**
     * @return string[] Message lines.
     */
    public function getMessages() : array
    {
        $suitName = $this->suit->fullNameRu();

        return [
            $this->sender . ' называет масть: <b>' . $suitName . '</b>',
            'Следующий игрок должен положить ' . $suitName
        ];
    }
}
