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
     * by {@see Word} entity.
     */
    public function loadByWord(Word $word) : ?DictWordInterface
    {
        return $this->getByWord($word, true);
    }

    /**
     * Returns external dictionary word by Word entity.
     * 
     * @param $allowRemoteLoad Set this to 'true' if the remote loading must
     * be enabled. By default it's not performed.
     */
    public function getByWord(
        Word $word,
        bool $allowRemoteLoad = false
    ) : ?DictWordInterface
    {
        // searching by word
        $dictWord = $word
            ? $this->dictWordRepository->getByWord($word)
            : null;

        $language = $word->language();
        $wordStr = $word->word;

        // searching by language & wordStr
        $dictWord ??= ($language && strlen($wordStr) > 0)
            ? $this->dictWordRepository->getByWordStr($language, $wordStr)
            : null;

        if (is_null($dictWord) && $allowRemoteLoad) {
            // no word found, loading from dictionary
            $dictWord = $this
                ->externalDictService
                ->loadFromDictionary($language, $wordStr);

            if ($dictWord) {
                $dictWord = $this->dictWordRepository->save($dictWord);

                if ($word) {
                    $this->link($dictWord, $word);
                }
            }
        }

        return $dictWord;
    }

    /**
     * Links dict word with word and emits {@see DictWordLinkedEvent}.
     * 
     * If dict word was already linked to another word, emits {@see DictWordUnlinkedEvent}
     * as well.
     */
    public function link(DictWordInterface $dictWord, Word $word) : DictWordInterface
    {
        $unlinkedWord = $dictWord->getLinkedWord();

        // nothing to do?
        if ($word->equals($unlinkedWord)) {
            return $dictWord;
        }

        $dictWord = $dictWord->linkWord($word);

        $dictWord = $this->dictWordRepository->save($dictWord);

        if ($unlinkedWord) {
            $this->eventDispatcher->dispatch(
                new DictWordUnlinkedEvent($dictWord, $unlinkedWord)
            );
        }

        $this->eventDispatcher->dispatch(
            new DictWordLinkedEvent($dictWord)
        );

        return $dictWord;
    }

    /**
     * Unlinks word from dict word and emits {@see DictWordUnlinkedEvent}.
     */
    public function unlink(DictWordInterface $dictWord) : DictWordInterface
    {
        $unlinkedWord = $dictWord->getLinkedWord();

        // nothing to do?
        if (is_null($unlinkedWord)) {
            return $dictWord;
        }

        $dictWord = $dictWord->unlinkWord();

        $dictWord = $this->dictWordRepository->save($dictWord);

        $this->eventDispatcher->dispatch(
            new DictWordUnlinkedEvent($dictWord, $unlinkedWord)
        );

        return $dictWord;
    }
}
