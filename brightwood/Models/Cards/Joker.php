<?php

namespace Brightwood\Models\Cards;

use Brightwood\Models\Cards\Interfaces\EquatableInterface;

class Joker extends Card
{
    private const NAME = '🃏';

    /**
     * @param string|null $lang 'ru' and 'en' are supported. null = 'en'.
     */
    public function name(?string $lang = null) : string
    {
        return self::NAME;
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

    /**
     * Tries to parse card as a joker. If unsuccessful, returns null.
     */
    public static function tryParse(?string $str) : ?self
    {
        return $str == self::NAME
            ? new self()
            : null;
    }

    public function isJoker() : bool
    {
        return true;
    }
}
