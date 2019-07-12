<?php

namespace App\Services;

use Plasticode\Events\EventProcessor;
use Plasticode\Util\Date;

use App\Events\AssociationApprovedEvent;
use App\Events\WordApprovedEvent;
use App\Events\WordFeedbackEvent;
use App\Events\WordMatureEvent;
use App\Events\WordOutOfDateEvent;
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
        return $this->recountAll($word);
    }

    /**
     * WordOutOfDateEvent event processing.
     */
    public function processWordOutOfDateEvent(WordOutOfDateEvent $event) : iterable
    {
        $word = $event->getWord();
        return $this->recountAll($word);
    }

    private function recountAll(Word $word) : iterable
    {
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
        $approved = ($score >= $threshold);

        $now = Date::dbNow();

        if ($word->isApproved() !== $approved) {
            $word->approved = $approved ? 1 : 0;
            $word->approvedUpdatedAt = $now;
        }

        $word->updatedAt = $now;

        return $word;
    }

    private function recountMature(Word $word) : Word
    {
        $threshold = $this->getSettings('words.mature_threshold');
        
        $score = $word->matures()->count();
        $mature = ($score >= $threshold);

        $now = Date::dbNow();

        if ($word->isMature() !== $mature) {
            $word->mature = $mature ? 1 : 0;
            $word->matureUpdatedAt = $now;
        }

        $word->updatedAt = $now;
        
        return $word;
    }
}
