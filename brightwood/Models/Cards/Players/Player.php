<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\NamedInterface;
use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Interfaces\EquatableInterface;
use Plasticode\Core\Security;

abstract class Player implements GenderedInterface, NamedInterface, EquatableInterface
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

    public function hand() : Hand
    {
        return $this->hand;
    }

    public function handSize() : int
    {
        return $this->hand->size();
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

    public function equals(?EquatableInterface $obj) : bool
    {
        return
            $obj
            && ($obj instanceof self)
            && ($this->id === $obj->id());
    }

    // NamedInterface

    abstract public function name() : string;

    // GenderedInterface

    abstract public function gender() : int;

    // toString

    public function __toString()
    {
        return $this->name();
    }
}
