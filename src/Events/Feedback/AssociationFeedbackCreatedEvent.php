<?php

namespace App\Events\Feedback;

use App\Models\AssociationFeedback;
use Plasticode\Events\Event;

class AssociationFeedbackCreatedEvent extends FeedbackEvent
{
    protected AssociationFeedback $feedback;

    public function __construct(
        AssociationFeedback $feedback,
        ?Event $parent = null
    )
    {
        parent::__construct($parent);

        $this->feedback = $feedback;
    }

    public function getFeedback() : AssociationFeedback
    {
        return $this->feedback;
    }

    public function getEntity() : AssociationFeedback
    {
        return $this->getFeedback();
    }
}
