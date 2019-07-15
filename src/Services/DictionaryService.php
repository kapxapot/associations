<?php

namespace App\Services;

use Plasticode\Contained;

use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;

class DictionaryService extends Contained
{
    public function getYandexDictWordByWord(Word $word) : ?YandexDictWord
    {
        return $this->getYandexDictWord($word->language(), $word->word);
    }

    public function getYandexDictWord(Language $language, string $wordStr) : ?YandexDictWord
    {
        $yandexLanguage = $this->languageToYandexDictFormat($language);

        if (is_null($yandexLanguage)) {
            return null;
        }

        $dictWord = YandexDictWord::getByWordStr($language, $wordStr);

        if (!is_null($dictWord)) {
            return $dictWord;
        }

        // load from dictionary
    }

    private function languageToYandexDictFormat(Language $language) : ?string
    {
        if ($language->getId() === Language::RUSSIAN) {
            return "ru-ru";
        }

        return null;
    }
}
