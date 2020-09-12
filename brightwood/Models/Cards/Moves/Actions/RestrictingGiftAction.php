<?php

namespace Brightwood\Models\Cards\Moves\Actions;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;

class RestrictingGiftAction extends GiftAction
{
    /** @var callable */
    private $restriction;

    /**
     * @param callable $restriction func(Card) : bool,
     * must return true if the card passes the restricting condition.
     */
    public function __construct(
        Player $sender,
        Card $gift,
        callable $restriction
    )
    {
        parent::__construct($sender, $gift);

        $this->restriction = $restriction;
    }

    public function isAllowed(Card $card) : bool
    {
        return ($this->restriction)($card);
    }
}
