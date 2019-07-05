<?php

namespace App\Services;

use Plasticode\Events\EventProcessor;
use Plasticode\Util\Date;

use App\Events\AssociationApprovedEvent;
use App\Events\WordApprovedEvent;
use App\Events\WordFeedbackEvent;
use App\Events\WordMatureEvent;
use App\Models\Word;

class WordRecountService extends EventProcessor
{
    /**
     * AssociationApprovedEvent event processing.
     */
    public function processAssociationApprovedEvent(AssociationApprovedEvent $event) : iterable
    {
        $assoc = $event->getAssociation();

        foreach ($assoc->words() as $word) {
            $word = $this->recountApproved($word);
            $word = $word->save();
    
            yield new WordApprovedEvent($word);
        }
    }

    /**
     * WordFeedbackEvent event processing.
     */
    public function processWordFeedbackEvent(WordFeedbackEvent $event) : iterable
    {
        $word = $event->getFeedback()->word();

        $word = $this->recountApproved($word);
        $word = $this->recountMature($word);
        $word = $word->save();

        yield new WordApprovedEvent($word);
        yield new WordMatureEvent($word);
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

        return $word;
    }

    private function recountMature(Word $word) : Word
    {
        $threshold = $this->getSettings('words.mature_threshold');
        
        $score = $word->matures()->count();
        
        $word->mature = ($score >= $threshold) ? 1 : 0;
        $word->matureUpdatedAt = Date::dbNow();
        
        return $word;
    }
}
