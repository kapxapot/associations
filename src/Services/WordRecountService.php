<?php

namespace App\Services;

use App\Events\Word\WordApprovedChangedEvent;
use App\Events\Word\WordCorrectedEvent;
use App\Events\Word\WordDisabledChangedEvent;
use App\Events\Word\WordMatureChangedEvent;
use App\Models\Word;
use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Specifications\WordSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Date;
use Psr\Log\LoggerInterface;

/**
 * @emits WordApprovedChangedEvent
 * @emits WordCorrectedEvent
 * @emits WordDisabledChangedEvent
 * @emits WordMatureChangedEvent
 */
class WordRecountService
{
    use LoggerAwareTrait;
    use ToBit;

    private WordRepositoryInterface $wordRepository;
    private WordRelationRepositoryInterface $wordRelationRepository;

    private WordSpecification $wordSpecification;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordRelationRepositoryInterface $wordRelationRepository,
        WordSpecification $wordSpecification,
        EventDispatcher $eventDispatcher,
        LoggerInterface $logger
    )
    {
        $this->wordRepository = $wordRepository;
        $this->wordRelationRepository = $wordRelationRepository;

        $this->wordSpecification = $wordSpecification;
        $this->eventDispatcher = $eventDispatcher;

        $this->logger = $logger;
    }

    public function recountAll(Word $word, ?Event $sourceEvent = null): Word
    {
        $word = $this->recountDisabled($word, $sourceEvent);
        $word = $this->recountApproved($word, $sourceEvent);
        $word = $this->recountMature($word, $sourceEvent);
        $word = $this->recountCorrectedWord($word, $sourceEvent);

        return $word;
    }

    public function recountDisabled(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $disabled = $this->wordSpecification->isDisabled($word);

        if ($word->isDisabled() !== $disabled) {
            $word->disabled = self::toBit($disabled);
            $word->disabledUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordDisabledChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    public function recountApproved(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $approved = $this->wordSpecification->isApproved($word);

        if ($word->isApproved() !== $approved) {
            $word->approved = self::toBit($approved);
            $word->approvedUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordApprovedChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    public function recountMature(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $mature = $this->wordSpecification->isMature($word);

        if ($word->isMature() !== $mature) {
            $word->mature = self::toBit($mature);
            $word->matureUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordMatureChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    public function recountCorrectedWord(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $correctedWord = $this->wordSpecification->correctedWord($word);

        if ($correctedWord !== $word->word) {
            $word->word = $correctedWord;
            $word->wordBin = $correctedWord;
            $word->wordUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordCorrectedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    /**
     * This function does two things:
     * 
     * - Ensures that the word has no more than one primary relation.
     * - Syncs the word's `mainId` with the primary relation.
     */
    public function recountRelations(Word $word, ?Event $sourceEvent = null): Word
    {
        $primary = $this->enforcePrimaryRelation($word);

        // update the word's `mainId`
        $mainId = $primary ? $primary->mainWordId : null;

        if ($word->mainId != $mainId) {
            $word->mainId = $mainId;
            $word->updatedAt = Date::dbNow();

            $this->wordRepository->save($word);
        }

        return $word;
    }

    /**
     * - Determine primary relation (and return it).
     * - Purge old primary relations if there are any.
     */
    private function enforcePrimaryRelation(Word $word): ?WordRelation
    {
        // need to reload relations, because they can be cached for `$word`
        $relations = $this
            ->wordRelationRepository
            ->getAllByWord($word)
            ->filterPrimary()
            ->descByUpdate();

        /** @var WordRelation|null */
        $primary = $relations->first();

        if ($relations->count() > 1) {
            $relations->except($primary)->apply(
                function (WordRelation $wr) {
                    $wr->primary = false;
                    $wr->updatedAt = Date::dbNow();

                    $this->wordRelationRepository->save($wr);
                }
            );
        }

        return $primary;
    }
}
