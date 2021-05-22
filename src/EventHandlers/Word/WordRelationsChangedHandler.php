<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordRelationsChangedEvent;
use App\Services\WordRecountService;

/**
 * Recounts relations for the word.
 */
class WordRelationsChangedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordRelationsChangedEvent $event) : void
    {
        $word = $event->getWord();

        $this->wordRecountService->recountRelations($word, $event);
    }
}
