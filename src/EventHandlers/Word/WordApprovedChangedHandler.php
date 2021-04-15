<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordApprovedChangedEvent;
use App\Models\Association;
use App\Services\AssociationRecountService;

/**
 * Recounts approved status for associations that use the word.
 * 
 * If the word has approved override (according to
 * {@see \App\Specifications\WordSpecification}), all its associations approved
 * status must be affected.
 */
class WordApprovedChangedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(WordApprovedChangedEvent $event) : void
    {
        $event
            ->getWord()
            ->associations()
            ->apply(
                fn (Association $a) =>
                $this->associationRecountService->recountApproved($a, $event)
            );
    }
}
