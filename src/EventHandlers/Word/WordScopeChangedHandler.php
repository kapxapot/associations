<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordScopeChangedEvent;
use App\Models\Association;
use App\Services\AssociationRecountService;

/**
 * Recounts associations that use the word.
 */
class WordScopeChangedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(WordScopeChangedEvent $event) : void
    {
        $event
            ->getWord()
            ->associations()
            ->apply(
                fn (Association $a) =>
                    $this->associationRecountService->recountAll($a, $event)
            );
    }
}
