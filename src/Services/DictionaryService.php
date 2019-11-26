<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Word;
use App\Services\Interfaces\ExternalDictServiceInterface;

class DictionaryService
{
    /**
     * @var \App\Services\Interfaces\ExternalDictServiceInterface
     */
    private $externalDictService;

    public function __construct(ExternalDictServiceInterface $externalDictService)
    {
        $this->externalDictService = $externalDictService;
    }

    public function isWordKnown(Word $word) : bool
    {
        $dictWord = $this->externalDictService->getWord($word);

        return !is_null($dictWord) && $dictWord->isValid();
    }

    public function isWordStrKnown(Language $language, string $wordStr) : bool
    {
        $dictWord = $this->externalDictService->getWordStr($language, $wordStr);

        return !is_null($dictWord) && $dictWord->isValid();
    }
}
