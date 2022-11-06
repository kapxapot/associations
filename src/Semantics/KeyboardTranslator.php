<?php

namespace App\Semantics;

use App\Models\Language;
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
            'lang_from' => Language::EN,
            'lang_to' => Language::RU,
            'from' => 'f,dult`;pbqrkvyjghcnea[wxio]m\'.z',
            'to' => 'абвгдеёжзийклмнопрстуфхцчшщъьэюя'
        ],
        self::RU_EN => [
            'lang_from' => Language::RU,
            'lang_to' => Language::EN,
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

    /**
     * Returns a translation direction INTO a provided language if there is any.
     */
    public function getTranslationDirection(string $langCode): ?string
    {
        foreach ($this->map as $dir => $settings) {
            if ($settings['lang_to'] === $langCode) {
                return $dir;
            }
        }

        return null;
    }
}
