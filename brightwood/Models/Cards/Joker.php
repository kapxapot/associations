<?php

namespace Brightwood\Models\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;

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

    public function equals(?EquatableInterface $obj) : bool
    {
        return $obj && ($obj instanceof self);
    }

    public function isJoker() : bool
    {
        return true;
    }
}
