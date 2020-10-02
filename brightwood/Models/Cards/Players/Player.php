<?php

namespace Brightwood\Models\Cards\Players;

use App\Models\Interfaces\GenderedInterface;
use App\Models\Interfaces\NamedInterface;
use Brightwood\Collections\Cards\CardCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Interfaces\EquatableInterface;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Core\Security;

abstract class Player implements GenderedInterface, NamedInterface, EquatableInterface, SerializableInterface
{
    protected ?string $id = null;
    protected ?string $icon = null;
    protected ?Hand $hand = null;
    protected bool $isInspector = false;

    public function id() : string
    {
        $this->id ??= Security::generateToken(10);

        return $this->id;
    }

    /**
     * @return static
     */
    public function withId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return static
     */
    public function withIcon(string $icon) : self
    {
        $this->icon = $icon;

        return $this;
    }

    public function hand() : Hand
    {
        $this->hand ??= new Hand();

        return $this->hand;
    }

    /**
     * @return static
     */
    public function withHand(Hand $hand) : self
    {
        $this->hand = $hand;

        return $this;
    }

    public function handSize() : int
    {
        return $this->hand()->size();
    }

    abstract public function isBot() : bool;

    public function addCards(CardCollection $cards) : void
    {
        $this->hand()->addMany($cards);
    }

    public function removeCard(Card $card) : void
    {
        $this->hand()->remove($card);
    }

    public function hasCard(Card $card) : bool
    {
        return $this->hand()->contains($card);
    }

    public function equals(?EquatableInterface $obj) : bool
    {
        return
            $obj
            && ($obj instanceof self)
            && ($this->id() === $obj->id());
    }

    public function isInspector() : bool
    {
        return $this->isInspector;
    }

    /**
     * @return static
     */
    public function withIsInspector(bool $isInspector) : self
    {
        $this->isInspector = $isInspector;

        return $this;
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

    public function __toString()
    {
        return $this->publicName();
    }

    // NamedInterface

    abstract public function name() : string;

    // GenderedInterface

    abstract public function gender() : int;

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return UniformSerializer::serialize(
            $this,
            [
                'id' => $this->id(),
                'icon' => $this->icon,
                'hand' => $this->hand(),
                'is_inspector' => $this->isInspector
            ],
            ...$data
        );
    }
}
