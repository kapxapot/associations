<?php

namespace Brightwood\Models\Cards;

class Joker extends Card
{
    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function name(?string $lang = null) : string
    {
        return '🃏';
    }

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function fullName(?string $lang = null) : string
    {
        $lang ??= 'en';

        switch ($lang) {
            case 'ru':
                return 'джокер';

            default:
                return 'joker';
        }
    }

    public function equals(?Card $card) : bool
    {
        return $card && ($card instanceof self);
    }
}
