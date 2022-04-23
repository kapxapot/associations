<?php

namespace App\Services;

use App\Events\Word\WordCorrectedEvent;
use App\Events\Word\WordScopeChangedEvent;
use App\Events\Word\WordSeverityChangedEvent;
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
 * @emits WordCorrectedEvent
 * @emits WordScopeChangedEvent
 * @emits WordSeverityChangedEvent
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
        $word = $this->recountRelations($word, $sourceEvent);
        $word = $this->recountSeverity($word, $sourceEvent);
        $word = $this->recountScope($word, $sourceEvent);
        $word = $this->recountCorrectedWord($word, $sourceEvent);
        $word = $this->recountMeta($word);

        return $word;
    }

    /**
     * Recounts all dependent words of the word.
     */
    public function recountDependents(Word $word, ?Event $sourceEvent = null): void
    {
        $word
            ->dependents()
            ->apply(
                fn (Word $w) => $this->recountAll($w, $sourceEvent)
            );
    }

    public function recountScope(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $scope = $this->wordSpecification->countScope($word);

        if ($word->scope != $scope) {
            $word->scope = $scope;
            $word->scopeUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordScopeChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    private function recountSeverity(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $severity = $this->wordSpecification->countSeverity($word);

        if ($word->severity != $severity) {
            $word->severity = $severity;
            $word->severityUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordRepository->save($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordSeverityChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }

    public function recountCorrectedWord(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $correctedWord = $this->wordSpecification->countCorrectedWord($word);

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

    public function recountMeta(Word $word): Word
    {
        $word->setMetaValue(
            Word::META_AGGREGATED_WORDS,
            $word->aggregatedWordIds(true)
        );

        return $this->wordRepository->save($word);
    }
}
