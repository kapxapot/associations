<?php

namespace App\EventHandlers\Override;

use App\Events\Override\AssociationOverrideCreatedEvent;
use App\Services\AssociationRecountService;

/**
 * Recounts all statuses (approved & mature, etc.) for the association
 * based on the association override.
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
