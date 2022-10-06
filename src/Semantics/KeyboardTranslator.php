<?php

namespace App\Semantics;

use Webmozart\Assert\Assert;

/**
 * Translates words based on the keyboard keys combination.
 *
 * Supported directions:
 *
 * - ru -> en
 * - en -> ru
 */
class KeyboardTranslator
{
    const RU_EN = 'ru_en';
    const EN_RU = 'en_ru';

    private array $map = [
        self::EN_RU => [
            'from' => 'f,dult`;pbqrkvyjghcnea[wxio]m\'.z',
            'to' => 'абвгдеёжзийклмнопрстуфхцчшщъьэюя'
        ],
        self::RU_EN => [
            'from' => 'фисвуапршолдьтщзйкыегмцчня',
            'to' => 'abcdefghijklmnopqrstuvwxyz'
        ]
    ];

    public function translate(string $direction, ?string $value): ?string
    {
        $map = $this->map[$direction] ?? null;

        Assert::notNull($map);

        if ($value === null) {
            return null;
        }

        $result = '';

        for ($i = 0; $i < mb_strlen($value); $i++) {
            $ch = mb_substr($value, $i, 1);
            $pos = mb_strpos($map['from'], $ch);

            if ($pos === false) {
                $result .= $ch;
            } else {
                $result .= mb_substr($map['to'], $pos, 1);
            }
        }

        return $result;
    }
}
