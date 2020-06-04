<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordCreatedEvent;
use App\Services\DictionaryService;

/**
 * On word creation look for dict word and map if exists.
 */
class WordCreatedHandler
{
    private DictionaryService $dictionaryService;

    public function __construct(
        DictionaryService $dictionaryService
    )
    {
        $this->dictionaryService = $dictionaryService;
    }

    public function __invoke(WordCreatedEvent $event) : void
    {
        $word = $event->getWord();

        $this->dictionaryService->loadByWord($word);
    }
}
