<?php

namespace Brightwood\Models\Cards\Players;

use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Hand;
use Plasticode\Core\Security;

abstract class Player
{
    protected string $id;
    protected Hand $hand;

    public function __construct()
    {
        $this->id = Security::generateToken(10);

        $this->hand = new Hand();
    }

    public function id() : string
    {
        return $this->id;
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

    public function equals(?self $other) : bool
    {
        return $other && ($this->id === $other->id());
    }

    public function __toString()
    {
        return $this->name();
    }
}
