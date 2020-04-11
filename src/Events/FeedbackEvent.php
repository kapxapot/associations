<?php

namespace App\Events;

use App\Models\Feedback;
use Plasticode\Events\Event;

abstract class FeedbackEvent extends Event
{
    protected Feedback $feedback;

    public function __construct(Feedback $feedback, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->feedback = $feedback;
    }

    public function getFeedback() : Feedback
    {
        return $this->feedback;
    }

    public function getEntity() : Feedback
    {
        return $this->getFeedback();
    }
}
