<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordRelationsChangedEvent;
use App\Services\AssociationRecountService;
use App\Services\WordRecountService;

/**
 * Recounts relations for the word and performs all related recounts.
 */
class WordRelationsChangedHandler
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

    public function __invoke(WordRelationsChangedEvent $event) : void
    {
        $word = $event->getWord();

        $this->wordRecountService->recountAll($word, $event);
        $this->associationRecountService->recountByWord($word, $event);
    }
}
