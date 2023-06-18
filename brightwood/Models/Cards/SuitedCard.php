<?php

namespace Brightwood\Models\Cards;

use Plasticode\Models\Interfaces\EquatableInterface;

class SuitedCard extends Card
{
    private Suit $suit;
    private Rank $rank;

    public function __construct(
        Suit $suit,
        Rank $rank
    )
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function suit(): Suit
    {
        return $this->suit;
    }

    public function rank(): Rank
    {
        return $this->rank;
    }

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function name(?string $lang = null): string
    {
        $lang ??= 'en';

        switch ($lang) {
            case 'ru':
                return $this->suit->symbol() . $this->rank->valueRu();

            default:
                return $this->suit->symbol() . $this->rank->value();
        }
    }

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function fullName(?string $lang = null): string
    {
        $lang ??= 'en';

        switch ($lang) {
            case 'ru':
                return $this->rank->nameRu() . ' ' . $this->suit->nameRuGen();

            default:
                return $this->rank->name() . ' of ' . $this->suit->name();
        }
    }

    public function equals(?EquatableInterface $obj): bool
    {
        return
            ($obj instanceof self)
            && $this->suit->equals($obj->suit())
            && $this->rank->equals($obj->rank());
    }

    /**
     * Tries to parse suited card. If unsuccessful, returns null.
     * 
     * ♥8
     */
    public static function tryParse(?string $str): ?self
    {
        if (mb_strlen($str) < 2) {
            return null;
        }

        $suitStr = mb_substr($str, 0, 1);
        $rankStr = mb_substr($str, 1);

        $suit = Suit::tryParse($suitStr);
        $rank = Rank::tryParse($rankStr);

        if (is_null($suit) || is_null($rank)) {
            return null;
        }

        return new self($suit, $rank);
    }

    public function isSuited(): bool
    {
        return true;
    }

    public function isSameSuit(self $card): bool
    {
        return $this->isSuit(
            $card->suit()
        );
    }

    public function isSameRank(self $card): bool
    {
        return $this->isRank(
            $card->rank()
        );
    }

    public function isSuit(Suit $suit): bool
    {
        return $this->suit->equals($suit);
    }

    public function isRank(Rank $rank): bool
    {
        return $this->rank->equals($rank);
    }
}
