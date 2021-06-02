<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordDisabledChangedEvent;
use App\Models\Association;
use App\Services\AssociationRecountService;

/**
 * Recounts associations that use the word.
 * 
 * If the word was disabled/enabled, all its associations disabled/approved
 * statuses must be recounted.
 */
class WordDisabledChangedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(WordDisabledChangedEvent $event) : void
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
