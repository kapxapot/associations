<?php

namespace App\EventHandlers\Feedback;

use App\Events\Feedback\WordFeedbackCreatedEvent;
use App\Services\WordRecountService;

/**
 * Recounts all statuses (approved & mature) for the word based on the feedback.
 */
class WordFeedbackCreatedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordFeedbackCreatedEvent $event) : void
    {
        $word = $event->getFeedback()->word();

        $this->wordRecountService->recountAll($word, $event);
    }
}
