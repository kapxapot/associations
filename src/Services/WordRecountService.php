<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Events\Event;
use Plasticode\Util\Date;

use App\Events\WordFeedbackEvent;
use App\Events\WordApprovedEvent;
use App\Events\WordMatureEvent;
use App\Models\Word;

class WordRecountService extends Contained
{
    public function processWordFeedbackEvent(WordFeedbackEvent $event) : iterable
    {
        $word = $event->getFeedback()->word();


        return new WordApprovedEvent($wordUpdated);
        yield $this->recountApproved($word);
        yield $this->recountMature($word);
    }

    private function recountApproved(Word $word) : Word
    {
        $assocCoeff = $this->getSettings('words.coeffs.approved_association');
        $dislikeCoeff = $this->getSettings('words.coeffs.dislike');
        $threshold = $this->getSettings('words.approval_threshold');
        
        $approvedAssocsCount = $word->approvedAssociations()->count();
        $dislikeCount = $word->dislikes()->count();
        
        $score = $approvedAssocsCount * $assocCoeff - $dislikeCount * $dislikeCoeff;
        
        $word->approved = ($score >= $threshold) ? 1 : 0;
        $word->approvedUpdatedAt = Date::dbNow();

        return $word->save();
    }

    private function recountMature(Word $word) : Event
    {
        $threshold = $this->getSettings('words.mature_threshold');
        
        $score = $this->matures()->count();
        
        $word->mature = ($score >= $threshold) ? 1 : 0;
        $word->matureUpdatedAt = Date::dbNow();
        
        $wordUpdated = $word->save();
        
        return new WordMatureEvent($wordUpdated);
    }
}
