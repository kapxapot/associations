<?php

namespace App\EventHandlers\Association;

use App\Events\Association\AssociationCreatedEvent;
use App\Services\AssociationRecountService;

/**
 * On association creation recount it.
 */
class AssociationCreatedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(
        AssociationRecountService $associationRecountService
    )
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(AssociationCreatedEvent $event) : void
    {
        $association = $event->getAssociation();

        $this->associationRecountService->recountAll($association, $event);
    }
}
