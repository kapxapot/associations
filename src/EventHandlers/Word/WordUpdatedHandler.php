<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordUpdatedEvent;
use App\Services\DictionaryService;

class WordUpdatedHandler
{
    private DictionaryService $dictionaryService;

    public function __construct(
        DictionaryService $dictionaryService
    )
    {
        $this->dictionaryService = $dictionaryService;
    }

    public function __invoke(WordUpdatedEvent $event) : void
    {
        $word = $event->getWord();

        $dictWord = $word->dictWord();

        if ($dictWord) {
            if ($dictWord->getWord() !== $word->word) {
                $this->dictionaryService->unlink($dictWord);

                $dictWord = null;
            }
        }

        if ($dictWord === null && $event->isSync()) {
            $this->dictionaryService->loadByWord($word);
        }
    }
}
