<?php

namespace App\EventHandlers\Feedback;

use App\Events\Feedback\WordFeedbackCreatedEvent;
use App\Models\Word;
use App\Services\WordRecountService;

/**
 * Recounts all word's attributes based on the feedback.
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

        $word
            ->dependents()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountAll($w, $event)
            );
    }
}
