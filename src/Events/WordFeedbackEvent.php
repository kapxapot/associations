<?php

namespace App\Events;

use Plasticode\Events\Event;

use App\Models\WordFeedback;

class WordFeedbackEvent extends Event
{
    private $feedback;

    public function __construct(WordFeedback $feedback, Event $parent = null)
    {
        parent::__construct($parent);
        
        $this->feedback = $feedback;
    }

    public function getFeedback() : WordFeedback
    {
        return $this->feedback;
    }
}
