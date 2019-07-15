<?php

namespace App\Services;

use Plasticode\Contained;

use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;

class DictionaryService extends Contained
{
    public function isWordKnown(Word $word) : bool
    {
        $dictWord = $this->yandexDictService->getWord($word);

        return !is_null($dictWord) && $dictWord->isValid();
    }

    public function isWordStrKnown(Language $language, string $wordStr) : bool
    {
        $dictWord = $this->yandexDictService->getWordStr($language, $wordStr);

        return !is_null($dictWord) && $dictWord->isValid();
    }
}
