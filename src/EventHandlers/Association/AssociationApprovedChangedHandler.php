<?php

namespace App\EventHandlers\Association;

use App\Events\Association\AssociationApprovedChangedEvent;
use App\Models\Word;
use App\Services\WordRecountService;

/**
 * Recounts approved status for words in the association.
 * 
 * If the association is approved, the words in it should be approved too.
 */
class AssociationApprovedChangedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(AssociationApprovedChangedEvent $event): void
    {
        $event
            ->getAssociation()
            ->words()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountApproved($w, $event)
            );
    }
}
