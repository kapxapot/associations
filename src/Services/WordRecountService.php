<?php

namespace App\Services;

use App\Events\AssociationApprovedEvent;
use App\Events\WordApprovedEvent;
use App\Events\WordFeedbackEvent;
use App\Events\WordMatureEvent;
use App\Events\WordOutOfDateEvent;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Specifications\WordSpecification;
use Plasticode\Events\EventProcessor;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;

class WordRecountService extends EventProcessor
{
    private WordRepositoryInterface $wordRepository;
    private WordSpecification $wordSpecification;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordSpecification $wordSpecification
    )
    {
        $this->wordRepository = $wordRepository;
        $this->wordSpecification = $wordSpecification;
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
        $approved = $this->wordSpecification->isApproved($word);

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
        $mature = $this->wordSpecification->isMature($word);

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
