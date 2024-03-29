<?php

namespace App\EventHandlers\Association;

use App\Events\Association\AssociationOutOfDateEvent;
use App\Services\AssociationRecountService;

/**
 * Recounts all association's attributes if it's out of date.
 */
class AssociationOutOfDateHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(AssociationOutOfDateEvent $event) : void
    {
        $assoc = $event->getAssociation();

        $this->associationRecountService->recountAll($assoc, $event);
    }
}
