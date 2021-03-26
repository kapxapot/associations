<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordCorrectedEvent;
use App\Services\DefinitionService;
use App\Services\DictionaryService;

class WordCorrectedHandler
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

    public function __invoke(WordCorrectedEvent $event) : void
    {
        $this->checkDictWord($event);
        $this->checkDefinition($event);
    }

    /**
     * 1. if the word has changed for some reason, dict word must be unlinked
     * 2. also, if there is no dict word and the event is synchronous,
     * the dict word must be loaded
     */
    private function checkDictWord(WordCorrectedEvent $event): void
    {
        $word = $event->getWord();
        $dictWord = $word->dictWord();

        if ($dictWord !== null && !$dictWord->matchesWord($word)) {
            $this->dictionaryService->unlink($dictWord);

            $dictWord = null;
        }

        if ($dictWord === null && $event->isSync()) {
            $this->dictionaryService->loadByWord($word);
        }
    }

    private function checkDefinition(WordCorrectedEvent $event): void
    {
        $word = $event->getWord();
        $definition = $word->definition();

        if ($definition !== null) {
            $this->definitionService->unlink($definition);

            $definition = null;
        }

        if ($definition === null && $event->isSync()) {
            $this->definitionService->loadByWord($word);
        }
    }
}
