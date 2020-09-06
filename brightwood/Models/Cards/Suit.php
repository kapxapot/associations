<?php

namespace Brightwood\Models\Cards;

use Brightwood\Collections\Cards\SuitCollection;

class Suit
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

    public function id() : int
    {
        return $this->id;
    }

    public function symbol() : string
    {
        return $this->symbol;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function nameRu() : string
    {
        return $this->nameRu;
    }

    public function nameRuGen() : string
    {
        return $this->nameRuGen;
    }

    public function equals(self $suit) : bool
    {
        return $this->id() == $suit->id();
    }

    public static function all() : SuitCollection
    {
        self::$suits ??= new SuitCollection(
            [
                new self(self::SPADES, '♠', 'spades', 'пики', 'пик'),
                new self(self::CLUBS, '♣', 'clubs', 'трефы', 'треф'),
                new self(self::HEARTS, '♥', 'hearts', 'червы', 'черв'),
                new self(self::DIAMONDS, '♦', 'diamonds', 'бубны', 'бубен')
            ]
        );

        return self::$suits;
    }

    public static function spades() : self
    {
        return self::all()->get(self::SPADES);
    }

    public static function clubs() : self
    {
        return self::all()->get(self::CLUBS);
    }

    public static function hearts() : self
    {
        return self::all()->get(self::HEARTS);
    }

    public static function diamonds() : self
    {
        return self::all()->get(self::DIAMONDS);
    }
}
