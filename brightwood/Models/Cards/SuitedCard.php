<?php

namespace Brightwood\Models\Cards;

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

    public function suit() : Suit
    {
        return $this->suit;
    }

    public function rank() : Rank
    {
        return $this->rank;
    }

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function name(?string $lang = null) : string
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
    public function fullName(?string $lang = null) : string
    {
        $lang ??= 'en';

        switch ($lang) {
            case 'ru':
                return $this->rank->nameRu() . ' ' . $this->suit->nameRuGen();

            default:
                return $this->rank->name() . ' of ' . $this->suit->name();
        }
    }

    public function equals(?Card $card) : bool
    {
        if (is_null($card)) {
            return false;
        }

        if (!($card instanceof self)) {
            return false;
        }

        return
            $this->suit->equals($card->suit())
            && $this->rank->equals($card->rank());
    }
}
