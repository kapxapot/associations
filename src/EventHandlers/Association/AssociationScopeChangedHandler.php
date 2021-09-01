<?php

namespace App\EventHandlers\Association;

use App\Events\Association\AssociationScopeChangedEvent;
use App\Models\Word;
use App\Services\WordRecountService;

/**
 * Recounts scope for words in the association.
 *
 * If the association is public, the words in it should be public too.
 */
class AssociationScopeChangedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(AssociationScopeChangedEvent $event): void
    {
        $event
            ->getAssociation()
            ->words()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountScope($w, $event)
            );
    }
}
