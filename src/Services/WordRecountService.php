<?php

namespace App\Services;

use App\Events\Word\WordApprovedChangedEvent;
use App\Events\Word\WordDisabledChangedEvent;
use App\Events\Word\WordMatureChangedEvent;
use App\Models\Word;
use App\Specifications\WordSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Util\Date;

/**
 * @emits WordApprovedChangedEvent
 * @emits WordMatureChangedEvent
 */
class WordRecountService
{
    use ToBit;

    private WordSpecification $wordSpecification;
    private WordService $wordService;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordSpecification $wordSpecification,
        WordService $wordService,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordSpecification = $wordSpecification;
        $this->wordService = $wordService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function recountAll(Word $word, ?Event $sourceEvent = null): Word
    {
        $word = $this->recountDisabled($word, $sourceEvent);
        $word = $this->recountApproved($word, $sourceEvent);
        $word = $this->recountMature($word, $sourceEvent);

        return $word;
    }

    public function recountDisabled(Word $word, ?Event $sourceEvent = null): Word
    {
        $now = Date::dbNow();
        $changed = false;

        $disabled = $this->wordSpecification->isDisabled($word);

        if (
            $word->isDisabled() !== $disabled
            || is_null($word->disabledUpdatedAt)
        ) {
            $word->disabled = self::toBit($disabled);
            $word->disabledUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordService->update($word);

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

        if (
            $word->isApproved() !== $approved
            || is_null($word->approvedUpdatedAt)
        ) {
            $word->approved = self::toBit($approved);
            $word->approvedUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordService->update($word);

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

        if (
            $word->isMature() !== $mature
            || is_null($word->matureUpdatedAt)
        ) {
            $word->mature = self::toBit($mature);
            $word->matureUpdatedAt = $now;

            $changed = true;
        }

        $word->updatedAt = $now;

        $word = $this->wordService->update($word);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new WordMatureChangedEvent($word, $sourceEvent)
            );
        }

        return $word;
    }
}
