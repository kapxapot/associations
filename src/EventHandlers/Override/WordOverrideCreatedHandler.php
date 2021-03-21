<?php

namespace App\EventHandlers\Override;

use App\Events\Override\WordOverrideCreatedEvent;
use App\Services\WordRecountService;

/**
 * Recounts all statuses (approved & mature) for the word based on the word override.
 */
class WordOverrideCreatedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordOverrideCreatedEvent $event) : void
    {
        $word = $event->getWordOverride()->word();

        $this->wordRecountService->recountAll($word, $event);
    }
}
