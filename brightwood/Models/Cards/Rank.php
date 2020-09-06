<?php

namespace Brightwood\Models\Cards;

use Brightwood\Collections\Cards\RankCollection;

class Rank
{
    private const ACE = 1;
    private const TWO = 2;
    private const THREE = 3;
    private const FOUR = 4;
    private const FIVE = 5;
    private const SIX = 6;
    private const SEVEN = 7;
    private const EIGHT = 8;
    private const NINE = 9;
    private const TEN = 10;
    private const JACK = 11;
    private const QUEEN = 12;
    private const KING = 13;

    private int $id;

    /** @var integer|string */
    private $value;

    private string $code;
    private string $name;
    private string $nameRu;

    /** @var integer|string */
    private $valueRu;

    private static ?RankCollection $ranks = null;

    /**
     * @param integer|string $value
     * @param integer|string|null $valueRu
     */
    public function __construct(
        int $id,
        $value,
        string $name,
        string $nameRu,
        $valueRu = null
    )
    {
        $this->id = $id;
        $this->value = $value;
        $this->code = $value;
        $this->name = $name;
        $this->nameRu = $nameRu;
        $this->valueRu = $valueRu ?? $value;
    }

    public function id() : int
    {
        return $this->id;
    }

    public function withCode(string $code) : self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return integer|string
     */
    public function value()
    {
        return $this->value;
    }

    public function code() : string
    {
        return $this->code;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function nameRu() : string
    {
        return $this->nameRu;
    }

    /**
     * @return integer|string
     */
    public function valueRu()
    {
        return $this->valueRu;
    }

    public function equals(self $rank) : bool
    {
        return $this->id() == $rank->id();
    }

    public static function all() : RankCollection
    {
        self::$ranks ??= new RankCollection(
            [
                new self(self::ACE, 'A', 'ace', 'туз', 'Т'),
                new self(self::TWO, 2, 'two', 'двойка'),
                new self(self::THREE, 3, 'three', 'тройка'),
                new self(self::FOUR, 4, 'four', 'четверка'),
                new self(self::FIVE, 5, 'five', 'пятерка'),
                new self(self::SIX, 6, 'six', 'шестерка'),
                new self(self::SEVEN, 7, 'seven', 'семерка'),
                new self(self::EIGHT, 8, 'eight', 'восьмерка'),
                new self(self::NINE, 9, 'nine', 'девятка'),
                (new self(self::TEN, 10, 'ten', 'десятка'))->withCode('T'),
                new self(self::JACK, 'J', 'jack', 'валет', 'В'),
                new self(self::QUEEN, 'Q', 'queen', 'дама', 'Д'),
                new self(self::KING, 'K', 'king', 'король', 'К')
            ]
        );

        return self::$ranks;
    }

    public static function ace() : self
    {
        return self::all()->get(self::ACE);
    }

    public static function two() : self
    {
        return self::all()->get(self::TWO);
    }

    public static function three() : self
    {
        return self::all()->get(self::ACE);
    }

    public static function four() : self
    {
        return self::all()->get(self::FOUR);
    }

    public static function five() : self
    {
        return self::all()->get(self::FIVE);
    }

    public static function six() : self
    {
        return self::all()->get(self::SIX);
    }

    public static function seven() : self
    {
        return self::all()->get(self::SEVEN);
    }

    public static function eight() : self
    {
        return self::all()->get(self::EIGHT);
    }

    public static function nine() : self
    {
        return self::all()->get(self::NINE);
    }

    public static function ten() : self
    {
        return self::all()->get(self::TEN);
    }

    public static function jack() : self
    {
        return self::all()->get(self::JACK);
    }

    public static function queen() : self
    {
        return self::all()->get(self::QUEEN);
    }

    public static function king() : self
    {
        return self::all()->get(self::KING);
    }
}
