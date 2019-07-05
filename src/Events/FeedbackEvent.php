<?php

namespace App\Events;

use Plasticode\Events\Event;
use Plasticode\Models\DbModel;

use App\Models\Feedback;

abstract class FeedbackEvent extends Event
{
    private $feedback;

    public function __construct(Feedback $feedback, Event $parent = null)
    {
        parent::__construct($parent);
        
        $this->feedback = $feedback;
    }

    public function getFeedback() : Feedback
    {
        return $this->feedback;
    }

    public function getEntity() : DbModel
    {
        return $this->getFeedback();
    }
}
