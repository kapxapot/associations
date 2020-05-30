<?php

namespace App\EventHandlers\Association;

use App\Events\Association\AssociationApprovedChangedEvent;
use App\Models\Word;
use App\Services\WordRecountService;

class AssociationApprovedChangedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(AssociationApprovedChangedEvent $event) : void
    {
        $event
            ->getAssociation()
            ->words()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountApproved($w, $event)
            );
    }
}
