<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Word;

class DictionaryService
{
    /**
     * @var \App\Services\YandexDictService
     */
    private $yandexDictService;

    public function __construct(YandexDictService $yandexDictService)
    {
        $this->yandexDictService = $yandexDictService;
    }

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
