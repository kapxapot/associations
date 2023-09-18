<?php

namespace Brightwood\Models\Cards;

use Brightwood\Collections\Cards\SuitCollection;
use InvalidArgumentException;
use JsonSerializable;
use Plasticode\Models\Interfaces\EquatableInterface;
use Webmozart\Assert\Assert;

class Suit implements EquatableInterface, JsonSerializable
{
    private const SPADES = 1;
    private const CLUBS = 2;
    private const HEARTS = 3;
    private const DIAMONDS = 4;

    private int $id;
    private string $symbol;
    private string $name;
    private string $nameRu;
    private string $nameRuGen;

    private static ?SuitCollection $suits = null;

    public function __construct(
        int $id,
        string $symbol,
        string $name,
        string $nameRu,
        string $nameRuGen
    )
    {
        $this->id = $id;
        $this->symbol = $symbol;
        $this->name = $name;
        $this->nameRu = $nameRu;
        $this->nameRuGen = $nameRuGen;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nameRu(): string
    {
        return $this->nameRu;
    }

    public function nameRuGen(): string
    {
        return $this->nameRuGen;
    }

    public function fullName(): string
    {
        return $this->symbol . ' ' . $this->name;
    }

    public function fullNameRu(): string
    {
        return $this->symbol . ' ' . $this->nameRu;
    }

    public function equals(?EquatableInterface $obj): bool
    {
        return ($obj instanceof self)
            && $this->id() === $obj->id();
    }

    public static function all(): SuitCollection
    {
        self::$suits ??= SuitCollection::collect(
            new self(self::SPADES, '♠', 'spades', 'пики', 'пик'),
            new self(self::CLUBS, '♣', 'clubs', 'трефы', 'треф'),
            new self(self::HEARTS, '♥', 'hearts', 'червы', 'черв'),
            new self(self::DIAMONDS, '♦', 'diamonds', 'бубны', 'бубен')
        );

        return self::$suits;
    }

    public static function random(): self
    {
        return self::all()->random();
    }

    public static function spades(): self
    {
        return self::get(self::SPADES);
    }

    public static function clubs(): self
    {
        return self::get(self::CLUBS);
    }

    public static function hearts(): self
    {
        return self::get(self::HEARTS);
    }

    public static function diamonds(): self
    {
        return self::get(self::DIAMONDS);
    }

    protected static function get(int $id): self
    {
        return self::all()->get($id);
    }

    /**
     * Parses a suit. If not successful, throws {@see InvalidArgumentException}.
     * 
     * @throws InvalidArgumentException
     */
    public static function parse(?string $str): self
    {
        $suit = self::tryParse($str);

        Assert::notNull(
            $suit,
            'Failed to parse the suit: ' . $str
        );

        return $suit;
    }

    /**
     * Tries to parse a suit. If not successful, returns null.
     */
    public static function tryParse(?string $str): ?self
    {
        return self::all()->first(
            fn (self $s) => $s->symbol == $str
        );
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->symbol;
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        return $this->toString();
    }
}
