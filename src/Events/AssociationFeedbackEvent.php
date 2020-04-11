<?php

namespace App\Events;

use App\Models\AssociationFeedback;
use Plasticode\Events\Event;

class AssociationFeedbackEvent extends FeedbackEvent
{
    protected AssociationFeedback $feedback;

    public function __construct(
        AssociationFeedback $feedback,
        ?Event $parent = null
    )
    {
        parent::__construct($feedback, $parent);
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
