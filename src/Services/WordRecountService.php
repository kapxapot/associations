<?php

namespace App\Services;

use App\Events\Word\WordApprovedChangedEvent;
use App\Events\Word\WordCorrectedEvent;
use App\Events\Word\WordDisabledChangedEvent;
use App\Events\Word\WordMatureChangedEvent;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Specifications\WordSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Util\Date;

/**
 * @emits WordApprovedChangedEvent
 * @emits WordCorrectedEvent
 * @emits WordDisabledChangedEvent
 * @emits WordMatureChangedEvent
 */
class WordRecountService
{
    use ToBit;

    private WordRepositoryInterface $wordRepository;
    private WordSpecification $wordSpecification;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordSpecification $wordSpecification,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordRepository = $wordRepository;
        $this->wordSpecification = $wordSpecification;
        $this->eventDispatcher = $eventDispatcher;
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
}
