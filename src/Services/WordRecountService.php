<?php

namespace App\Services;

use App\Config\Interfaces\WordConfigInterface;
use App\Events\AssociationApprovedEvent;
use App\Events\WordApprovedEvent;
use App\Events\WordFeedbackEvent;
use App\Events\WordMatureEvent;
use App\Events\WordOutOfDateEvent;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\EventProcessor;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;

class WordRecountService extends EventProcessor
{
    private WordRepositoryInterface $wordRepository;
    private WordConfigInterface $config;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordConfigInterface $config
    )
    {
        $this->wordRepository = $wordRepository;
        $this->config = $config;
    }

    /**
     * AssociationApprovedEvent event processing.
     */
    public function processAssociationApprovedEvent(
        AssociationApprovedEvent $event
    ) : iterable
    {
        $assoc = $event->getAssociation();

        foreach ($assoc->words() as $word) {
            $word = $this->recountApproved($word);

            $word = $this->wordRepository->save($word);

            yield new WordApprovedEvent($word);
        }
    }

    /**
     * WordFeedbackEvent event processing.
     */
    public function processWordFeedbackEvent(WordFeedbackEvent $event) : iterable
    {
        $feedback = $event->getFeedback();
        $word = $feedback->word();

        return $this->recountAll($word);
    }

    /**
     * WordOutOfDateEvent event processing.
     */
    public function processWordOutOfDateEvent(
        WordOutOfDateEvent $event
    ) : iterable
    {
        $word = $event->getWord();
        return $this->recountAll($word);
    }

    private function recountAll(Word $word) : iterable
    {
        $word = $this->recountApproved($word);
        $word = $this->recountMature($word);

        $word = $this->wordRepository->save($word);

        yield new WordApprovedEvent($word);
        yield new WordMatureEvent($word);
    }

    private function recountApproved(Word $word) : Word
    {
        $assocCoeff = $this->config->wordApprovedAssociationCoeff();
        $dislikeCoeff = $this->config->wordDislikeCoeff();
        $threshold = $this->config->wordApprovalThreshold();

        $approvedAssocs = $word->approvedAssociations()->count();
        $dislikes = $word->dislikes()->count();

        $score = $approvedAssocs * $assocCoeff - $dislikes * $dislikeCoeff;

        $approved = ($score >= $threshold);

        $now = Date::dbNow();

        if (
            $word->isApproved() !== $approved
            || is_null($word->approvedUpdatedAt)
        ) {
            $word->approved = Convert::toBit($approved);
            $word->approvedUpdatedAt = $now;
        }

        $word->updatedAt = $now;

        return $word;
    }

    private function recountMature(Word $word) : Word
    {
        $threshold = $this->config->wordMatureThreshold();

        $score = $word->matures()->count();
        $mature = ($score >= $threshold);

        $now = Date::dbNow();

        if (
            $word->isMature() !== $mature
            || is_null($word->matureUpdatedAt)
        ) {
            $word->mature = Convert::toBit($mature);
            $word->matureUpdatedAt = $now;
        }

        $word->updatedAt = $now;

        return $word;
    }
}
