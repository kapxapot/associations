<?php

namespace App\EventHandlers\Override;

use App\Events\Override\AssociationOverrideCreatedEvent;
use App\Services\AssociationRecountService;

/**
 * Recounts all association's attributes based on its override.
 */
class AssociationOverrideCreatedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(AssociationOverrideCreatedEvent $event) : void
    {
        $association = $event->getAssociationOverride()->association();

        $this->associationRecountService->recountAll($association, $event);
    }
}
