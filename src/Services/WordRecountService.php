<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Events\WordMatureUpdatedEvent;
use App\Models\Word;

class WordRecountService extends Contained
{
    public function recountApproved(Word $word) : Word
    {
        $assocCoeff = $this->getSettings('words.coeffs.approved_association');
        $dislikeCoeff = $this->getSettings('words.coeffs.dislike');
        $threshold = $this->getSettings('words.approval_threshold');
        
        $approvedAssocsCount = $word->approvedAssociations()->count();
        $dislikeCount = $word->dislikes()->count();
        
        $score = $approvedAssocsCount * $assocCoeff - $dislikeCount * $dislikeCoeff;
        
        $word->approved = ($score >= $threshold) ? 1 : 0;
        $word->approvedUpdatedAt = Date::dbNow();
        $word->save();
    }

    public function recountMature(Word $word) : void
    {
        $threshold = $this->getSettings('words.mature_threshold');
        
        $score = $this->matures()->count();
        
        $word->mature = ($score >= $threshold) ? 1 : 0;
        $word->matureUpdatedAt = Date::dbNow();
        
        $wordUpdated = $word->save();
        
        $this->dispatcher->dispatch(new WordMatureUpdatedEvent($wordUpdated));
    }
}
