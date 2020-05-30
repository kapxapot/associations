<?php

namespace App\EventHandlers\Turn;

use App\Events\Turn\TurnCreatedEvent;
use App\Services\AssociationRecountService;

/**
 * Recounts approved for the association if it's present in the created turn.
 */
class TurnCreatedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(TurnCreatedEvent $event) : void
    {
        $assoc = $event->getTurn()->association();

        if ($assoc) {
            $assoc = $this->associationRecountService->recountApproved($assoc, $event);
        }
    }
}
