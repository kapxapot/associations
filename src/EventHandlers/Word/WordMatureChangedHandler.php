<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordMatureChangedEvent;
use App\Models\Association;
use App\Services\AssociationRecountService;

/**
 * Recounts mature status for associations that use the word.
 * 
 * If the word is mature, all its associations must be mature.
 */
class WordMatureChangedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(WordMatureChangedEvent $event) : void
    {
        $event
            ->getWord()
            ->associations()
            ->apply(
                fn (Association $a) =>
                $this->associationRecountService->recountMature($a, $event)
            );
        }
    }
}
