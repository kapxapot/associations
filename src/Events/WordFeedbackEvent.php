<?php

namespace App\Events;

use App\Models\WordFeedback;
use Plasticode\Events\Event;

class WordFeedbackEvent extends FeedbackEvent
{
    private WordFeedback $feedback;

    public function __construct(
        WordFeedback $feedback,
        ?Event $parent = null
    )
    {
        parent::__construct($feedback, $parent);
    }

    public function getFeedback() : WordFeedback
    {
        return $this->feedback;
    }

    public function getEntity() : WordFeedback
    {
        return $this->getFeedback();
    }
}
