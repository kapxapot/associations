<?php

namespace App\Services;

use Plasticode\Events\EventProcessor;
use Plasticode\Util\Date;

use App\Events\AssociationApprovedEvent;
use App\Events\AssociationFeedbackEvent;
use App\Events\AssociationMatureEvent;
use App\Events\NewTurnEvent;
use App\Events\WordMatureEvent;
use App\Models\Association;

class AssociationRecountService extends EventProcessor
{
    /**
     * NewTurnEvent event processing.
     */
    public function processNewTurnEvent(NewTurnEvent $event) : iterable
    {
        $assoc = $event->getTurn()->association();

        $assoc = $this->recountApproved($assoc);
        $assoc = $assoc->save();

        yield new AssociationApprovedEvent($assoc);
    }

    /**
     * AssociationFeedbackEvent event processing.
     */
    public function processAssociationFeedbackEvent(AssociationFeedbackEvent $event) : iterable
    {
        $assoc = $event->getFeedback()->association();

        $assoc = $this->recountApproved($assoc);
        $assoc = $this->recountMature($assoc);
        $assoc = $assoc->save();

        yield new AssociationApprovedEvent($assoc);
        yield new AssociationMatureEvent($assoc);
    }

    /**
     * WordMatureEvent event processing.
     */
    public function processWordMatureEvent(WordMatureEvent $event) : iterable
    {
        $word = $event->getWord();

        foreach ($word->associations() as $assoc) {
            $assoc = $this->recountMature($assoc);
            $assoc = $assoc->save();
    
            yield new AssociationMatureEvent($assoc);
        }
    }

    public function recountApproved(Association $assoc) : Association
    {
        $usageCoeff = self::getSettings('associations.coeffs.usage');
        $dislikeCoeff = self::getSettings('associations.coeffs.dislike');
        $threshold = self::getSettings('associations.approval_threshold');
        
        $turnsByUsers = $assoc->turnsByUsers();
        $turnCount = count($turnsByUsers);
        
        $dislikeCount = $assoc->dislikes()->count();
        
        $score = $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;

        $assoc->approved = ($score >= $threshold) ? 1 : 0;

        return $assoc;
    }

    public function recountMature(Association $assoc) : Association
    {
        if ($assoc->firstWord()->isMature() || $assoc->secondWord()->isMature()) {
            $mature = true;
        }
        else {
            $threshold = self::getSettings('associations.mature_threshold');

            $maturesCount = $assoc->matures()->count();

            $mature = ($maturesCount >= $threshold);
        }

        $assoc->mature = $mature ? 1 : 0;

        return $assoc;
    }
}
