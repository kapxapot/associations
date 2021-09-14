<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordSeverityChangedEvent;
use App\Services\AssociationRecountService;
use App\Services\WordRecountService;

/**
 * If the word is not neutral, all its associations and dependent words must have
 * the same (or higher) severity.
 */
class WordSeverityChangedHandler
{
    private AssociationRecountService $associationRecountService;
    private WordRecountService $wordRecountService;

    public function __construct(
        AssociationRecountService $associationRecountService,
        WordRecountService $wordRecountService
    )
    {
        $this->associationRecountService = $associationRecountService;
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordSeverityChangedEvent $event) : void
    {
        $word = $event->getWord();

        $this->associationRecountService->recountByWord($word, $event);
        $this->wordRecountService->recountDependents($word, $event);
    }
}
