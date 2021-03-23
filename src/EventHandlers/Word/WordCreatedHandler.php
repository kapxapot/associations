<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordCreatedEvent;
use App\Services\DefinitionService;
use App\Services\DictionaryService;

/**
 * On word creation look for dict word and map if exists.
 */
class WordCreatedHandler
{
    private DefinitionService $definitionService;
    private DictionaryService $dictionaryService;

    public function __construct(
        DefinitionService $definitionService,
        DictionaryService $dictionaryService
    )
    {
        $this->definitionService = $definitionService;
        $this->dictionaryService = $dictionaryService;
    }

    public function __invoke(WordCreatedEvent $event) : void
    {
        if ($event->isSync()) {
            $word = $event->getWord();

            $this->dictionaryService->loadByWord($word);
            $this->definitionService->loadByWord($word);
        }
    }
}
