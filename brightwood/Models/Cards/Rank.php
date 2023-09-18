<?php

namespace Brightwood\Models\Cards;

use Brightwood\Collections\Cards\RankCollection;
use Plasticode\Models\Interfaces\EquatableInterface;

class Rank implements EquatableInterface
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

    /** @var int|string */
    private $value;

    private string $code;
    private string $name;
    private string $nameRu;

    /** @var int|string */
    private $valueRu;

    private static ?RankCollection $ranks = null;

    /**
     * @param int|string $value
     * @param int|string|null $valueRu
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

    public function id(): int
    {
        return $this->id;
    }

    public function withCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int|string
     */
    public function value()
    {
        return $this->value;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nameRu(): string
    {
        return $this->nameRu;
    }

    /**
     * @return int|string
     */
    public function valueRu()
    {
        return $this->valueRu;
    }

    public function equals(?EquatableInterface $obj): bool
    {
        return ($obj instanceof self)
            && $this->id() === $obj->id();
    }

    public static function all(): RankCollection
    {
        self::$ranks ??= RankCollection::collect(
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
        );

        return self::$ranks;
    }

    public static function ace(): self
    {
        return self::get(self::ACE);
    }

    public static function two(): self
    {
        return self::get(self::TWO);
    }

    public static function three(): self
    {
        return self::get(self::THREE);
    }

    public static function four(): self
    {
        return self::get(self::FOUR);
    }

    public static function five(): self
    {
        return self::get(self::FIVE);
    }

    public static function six(): self
    {
        return self::get(self::SIX);
    }

    public static function seven(): self
    {
        return self::get(self::SEVEN);
    }

    public static function eight(): self
    {
        return self::get(self::EIGHT);
    }

    public static function nine(): self
    {
        return self::get(self::NINE);
    }

    public static function ten(): self
    {
        return self::get(self::TEN);
    }

    public static function jack(): self
    {
        return self::get(self::JACK);
    }

    public static function queen(): self
    {
        return self::get(self::QUEEN);
    }

    public static function king(): self
    {
        return self::get(self::KING);
    }

    protected static function get(int $id): self
    {
        return self::all()->get($id);
    }

    /**
     * Tries to parse rank. If not successful, returns null.
     */
    public static function tryParse(?string $str): ?self
    {
        return self::all()->first(
            fn (self $r) => $r->is($str)
        );
    }

    public function is(?string $str): bool
    {
        $values = [
            $this->code,
            (string)$this->value,
            (string)$this->valueRu
        ];

        foreach ($values as $value) {
            if (mb_strtolower($value) === mb_strtolower($str)) {
                return true;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }
}
