<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordOutOfDateEvent;
use App\Services\WordRecountService;

/**
 * Recounts all statuses (approved & mature) for the word if it's out of date.
 */
class WordOutOfDateHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordOutOfDateEvent $event) : void
    {
        $word = $event->getWord();

        $this->wordRecountService->recountAll($word, $event);
    }
}
