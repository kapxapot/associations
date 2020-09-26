<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\NamedInterface;
use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Core\Security;

abstract class Player implements GenderedInterface, NamedInterface, EquatableInterface
{
    protected string $id;
    protected ?string $icon = null;

    protected Hand $hand;

    protected bool $isInspector = false;

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

    /**
     * @return static
     */
    public function asInspector() : self
    {
        $this->isInspector = true;

        return $this;
    }

    public function isInspector() : bool
    {
        return $this->isInspector;
    }

    public function nameFor(?self $other) : string
    {
        return $this->equals($other)
            ? $this->personalName()
            : $this->publicName();
    }

    public function personalName() : string
    {
        return $this->iconize('Вы');
    }

    public function publicName() : string
    {
        return $this->iconize(
            $this->name()
        );
    }

    /**
     * Adds the icon to the name if it's defined.
     */
    protected function iconize(string $name) : string
    {
        return
            Collection::collect(
                $this->icon,
                $name
            )
            ->clean()
            ->join(' ');
    }

    /**
     * Returns player's name with hand size.
     */
    public function handString() : string
    {
        return $this . ' (' . $this->handSize() . ')';
    }

    // NamedInterface

    abstract public function name() : string;

    // GenderedInterface

    abstract public function gender() : int;

    // toString

    public function __toString()
    {
        return $this->publicName();
    }
}
