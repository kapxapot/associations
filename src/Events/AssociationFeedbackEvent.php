<?php

namespace App\Events;

use Plasticode\Events\Event;

use App\Models\AssociationFeedback;

class AssociationFeedbackEvent extends Event
{
    private $feedback;

    public function __construct(AssociationFeedback $feedback, Event $parent = null)
    {
        parent::__construct($parent);

        $this->feedback = $feedback;
    }

    public function getFeedback() : AssociationFeedback
    {
        return $this->feedback;
    }
}
