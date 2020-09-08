<?php

namespace Brightwood\Models\Cards\Players;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Hand;

abstract class Player
{
    protected Hand $hand;

    public function __construct()
    {
        $this->hand = new Hand();
    }

    abstract public function name() : string;

    public function hand() : Hand
    {
        return $this->hand;
    }

    abstract public function isBot() : bool;

    public function addCards(CardCollection $cards) : void
    {
        $this->hand->addMany($cards);
    }

    public function removeCard(Card $card) : void
    {
        $this->hand->remove($card);
    }

    public function hasCard(Card $card) : bool
    {
        return $this->hand->contains($card);
    }
}
