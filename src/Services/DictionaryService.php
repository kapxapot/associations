<?php

namespace App\Services;

use App\Events\DictWord\DictWordLinkedEvent;
use App\Events\DictWord\DictWordUnlinkedEvent;
use App\Models\Interfaces\DictWordInterface;
use App\Models\Word;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;
use Plasticode\Events\EventDispatcher;

/**
 * @emits DictWordLinkedEvent
 * @emits DictWordUnlinkedEvent
 */
class DictionaryService
{
    private DictWordRepositoryInterface $dictWordRepository;
    private ExternalDictServiceInterface $externalDictService;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        ExternalDictServiceInterface $externalDictService,
        EventDispatcher $eventDispatcher
    )
    {
        $this->dictWordRepository = $dictWordRepository;
        $this->externalDictService = $externalDictService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns (and loads it from remote dictionary) external dictionary word
     * by {@see Word}.
     */
    public function loadByWord(Word $word): ?DictWordInterface
    {
        return $this->getByWord($word, true);
    }

    /**
     * Returns external dictionary word by {@see Word}.
     *
     * @param $allowRemoteLoad Set this to `true` if the remote loading must
     * be enabled. By default it's not performed.
     */
    public function getByWord(
        Word $word,
        bool $allowRemoteLoad = false
    ): ?DictWordInterface
    {
        $language = $word->language();
        $wordStr = $word->word;

        $dictWord = $this->dictWordRepository->getByWord($word)
            ?? $this->dictWordRepository->getByWordStr($language, $wordStr);

        if ($dictWord === null && $allowRemoteLoad) {
            // no word found, loading from dictionary
            $dictWord = $this
                ->externalDictService
                ->loadFromDictionary($language, $wordStr);

            if ($dictWord !== null) {
                $dictWord = $this->dictWordRepository->save($dictWord);
            }
        }

        if ($dictWord !== null && !$word->equals($dictWord->getLinkedWord())) {
            $this->link($dictWord, $word);
        }

        return $dictWord;
    }

    /**
     * Links dict word to word and emits {@see DictWordLinkedEvent}.
     * 
     * If dict word was already linked to another word, emits {@see DictWordUnlinkedEvent}
     * as well.
     */
    public function link(DictWordInterface $dictWord, Word $word): DictWordInterface
    {
        $wordToUnlink = $dictWord->getLinkedWord();

        // nothing to do?
        if ($word->equals($wordToUnlink)) {
            return $dictWord;
        }

        $dictWord = $dictWord->linkWord($word);
        $dictWord = $this->dictWordRepository->save($dictWord);

        $word = $word->withDictWord($dictWord);

        if ($wordToUnlink !== null) {
            $this->unlinkWord($dictWord, $wordToUnlink);
        }

        $this->eventDispatcher->dispatch(
            new DictWordLinkedEvent($dictWord)
        );

        return $dictWord;
    }

    /**
     * Unlinks word from dict word and emits {@see DictWordUnlinkedEvent}.
     */
    public function unlink(DictWordInterface $dictWord): DictWordInterface
    {
        $wordToUnlink = $dictWord->getLinkedWord();

        // nothing to do?
        if (is_null($wordToUnlink)) {
            return $dictWord;
        }

        $dictWord = $dictWord->unlinkWord();

        $dictWord = $this->dictWordRepository->save($dictWord);

        $this->unlinkWord($dictWord, $wordToUnlink);

        return $dictWord;
    }

    private function unlinkWord(DictWordInterface $dictWord, Word $word): void
    {
        $word = $word->withDictWord(null);

        $this->eventDispatcher->dispatch(
            new DictWordUnlinkedEvent($dictWord, $word)
        );
    }
}
