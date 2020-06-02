<?php

namespace App\Events\Feedback;

use App\Models\WordFeedback;
use Plasticode\Events\Event;

class WordFeedbackCreatedEvent extends FeedbackEvent
{
    protected WordFeedback $feedback;

    public function __construct(
        WordFeedback $feedback,
        ?Event $parent = null
    )
    {
        parent::__construct($parent);

        $this->feedback = $feedback;
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
