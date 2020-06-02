<?php

namespace App\Events\Feedback;

use App\Models\Feedback;
use Plasticode\Events\EntityEvent;

abstract class FeedbackEvent extends EntityEvent
{
    abstract public function getFeedback() : Feedback;

    public function getEntity() : Feedback
    {
        return $this->getFeedback();
    }
}
