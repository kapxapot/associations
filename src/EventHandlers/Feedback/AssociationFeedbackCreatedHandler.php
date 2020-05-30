<?php

namespace App\EventHandlers\Feedback;

use App\Events\Feedback\AssociationFeedbackCreatedEvent;
use App\Services\AssociationRecountService;

/**
 * Recounts all (approved & mature) for the association based on the feedback.
 */
class AssociationFeedbackCreatedHandler
{
    private AssociationRecountService $associationRecountService;

    public function __construct(AssociationRecountService $associationRecountService)
    {
        $this->associationRecountService = $associationRecountService;
    }

    public function __invoke(AssociationFeedbackCreatedEvent $event) : void
    {
        $assoc = $event->getFeedback()->association();

        $this->associationRecountService->recountAll($assoc, $event);
    }
}
