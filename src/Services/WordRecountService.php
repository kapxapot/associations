<?php

namespace App\Services;

use App\Events\Word\WordApprovedChangedEvent;
use App\Events\Word\WordMatureChangedEvent;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Specifications\WordSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Events\EventProcessor;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;

class WordRecountService extends EventProcessor
{
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

    public function recountAll(Word $word, ?Event $sourceEvent = null) : Word
    {
        $word = $this->recountApproved($word, $sourceEvent);
        $word = $this->recountMature($word, $sourceEvent);

        return $word;
    }

    public function recountApproved(Word $word, ?Event $sourceEvent = null) : Word
    {
        $now = Date::dbNow();
        $changed = false;

        $approved = $this->wordSpecification->isApproved($word);

        if (
            $word->isApproved() !== $approved
            || is_null($word->approvedUpdatedAt)
        ) {
            $word->approved = Convert::toBit($approved);
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

    public function recountMature(Word $word, ?Event $sourceEvent = null) : Word
    {
        $now = Date::dbNow();
        $changed = false;

        $mature = $this->wordSpecification->isMature($word);

        if (
            $word->isMature() !== $mature
            || is_null($word->matureUpdatedAt)
        ) {
            $word->mature = Convert::toBit($mature);
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
}
