<?php

namespace App\EventHandlers\Override;

use App\Events\Override\WordOverrideCreatedEvent;
use App\Models\Association;
use App\Services\AssociationRecountService;
use App\Services\WordRecountService;

/**
 * Recounts all word's attributes based on its override.
 */
class WordOverrideCreatedHandler
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

    public function __invoke(WordOverrideCreatedEvent $event) : void
    {
        $word = $event->getWordOverride()->word();

        $this->wordRecountService->recountAll($word, $event);

        $word
            ->associations()
            ->apply(
                fn (Association $a) =>
                    $this->associationRecountService->recountAll($a, $event)
            );
    }
}
